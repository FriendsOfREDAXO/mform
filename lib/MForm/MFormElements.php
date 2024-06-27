<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use FriendsOfRedaxo\MForm\Handler\MFormAttributeHandler;
use FriendsOfRedaxo\MForm\Handler\MFormElementHandler;
use FriendsOfRedaxo\MForm\Handler\MFormOptionHandler;
use FriendsOfRedaxo\MForm\Handler\MFormParameterHandler;
use FriendsOfRedaxo\MForm\Handler\MFormValueHandler;
use FriendsOfRedaxo\MForm\Inputs\MFormInputsInterface;
use rex_addon;
use rex_be_controller;

use rex_path;
use function array_key_exists;
use function count;
use function is_array;
use function is_callable;
use function is_int;

abstract class MFormElements
{
    /** @var MFormItem[] */
    private array $items = [];

    private MFormItem $item;

    private array $result = [];

    /**
     * @description this class contains all addFormElement and setElementOptions methods like addTextField, setLabel, addSelectField, setOptions
     */
    public function __construct()
    {
        if (('edit' === rex_request('function', 'string') && 'add' != rex_request('function', 'string')) || ('content/edit' === rex_be_controller::getCurrentPage() && 'add' != rex_request('function', 'string'))) {            // load rex vars
            $this->result = MFormValueHandler::loadRexVars();
        }
    }

    /**
     * @description method to generate element array - add fields
     */
    public function addElement(string $type, float|int|string $id = null, string $value = null, array $attributes = null, array $options = null, array $parameter = null, ?int $catId = null, ?string $defaultValue = null): MForm
    {
        // remove ,
        if (!is_int($id)) {
            $id = str_replace(',', '.', (string) $id);
        }

        // create item element
        $this->item = MFormElementHandler::createElement(count($this->items) + 1, $type, $id);
        $this->items[$this->item->getId()] = $this->item; // add item element to items array

        // execute to set default value and / or loaded value
        MFormValueHandler::decorateItem($this->item, $this->result, $value, $defaultValue);

        $this->setCategory($catId);

        if (is_array($attributes) && count($attributes) > 0) {
            $this->setAttributes($attributes);
        }
        if (is_array($options) && count($options) > 0) {
            $this->setOptions($options);
        }
        if (is_array($parameter) && count($parameter) > 0) {
            $this->setParameters($parameter);
        }

        return $this;
    }

    public function addHtml(?string $html = null): MForm
    {
        return $this->addElement('html', null, $html);
    }

    public function addHeadline(?string $value = null, array $attributes = null): MForm
    {
        return $this->addElement('headline', null, $value, $attributes);
    }

    public function addDescription(?string $value = null): MForm
    {
        return $this->addElement('description', null, $value);
    }

    public function addAlert(string $key, ?string $value = null): MForm
    {
        return $this->addElement('alert', null, $value, ['class' => 'alert-' . $key]);
    }

    public function addAlertInfo(?string $value = null): MForm
    {
        return $this->addAlert('info', $value);
    }

    public function addAlertWarning(?string $value = null): MForm
    {
        return $this->addAlert('warning', $value);
    }

    public function addAlertDanger(?string $value = null): MForm
    {
        return $this->addAlert('danger', $value);
    }

    public function addAlertError(?string $value = null): MForm
    {
        return $this->addAlertDanger($value);
    }

    public function addAlertSuccess(?string $value = null): MForm
    {
        return $this->addAlert('success', $value);
    }

    public function addForm(callable|MForm|string $form = null, bool $parse = false, bool $debug = false): MForm
    {
        if (!$form instanceof MForm && is_callable($form)) {
            $form = $form();
        }
        if ($form instanceof MForm) {
            $form->setDebug($debug);
            if (!$parse) {
                $this->items[] = $form;
                return $this;
            }
            $form = $form->show();
        }
        return $this->addHtml($form);
    }

    public function addFieldsetArea(string $legend = null, $form = null, array $attributes = [], bool $parse = false): MForm
    {
        return $this->addElement('fieldset', null, null, array_merge(['legend' => $legend], $attributes))
            ->addForm($form, $parse)
            ->addElement('close-fieldset', null, null, $attributes);
    }

    public function addColumnElement(int $col, $form = null, array $attributes = [], bool $parse = false): MForm
    {
        if (!array_key_exists('class', $attributes) || (isset($attributes['class']) && !str_contains($attributes['class'], 'col-'))) {
            $attributes['class'] = "col-sm-$col" . ((isset($attributes['class'])) ? ' ' . $attributes['class'] : '');
        }
        return $this->addElement('column', null, null, $attributes)
            ->addForm($form, $parse)
            ->addElement('close-column', null, null, $attributes);
    }

    public function addInlineElement(string $label = '', $form = null, array $attributes = [], bool $parse = false): MForm
    {
        return $this->addElement('inline', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-inline', null, null, $attributes);
    }

    public function addTabElement(string $label = '', $form = null, bool $openTab = false, bool $pullNaviItemRight = false, array $attributes = [], bool $parse = false): MForm
    {
        $attributes = array_merge($attributes, ['data-group-open-tab' => $openTab, 'pull-right' => $pullNaviItemRight]);
        return $this->addElement('tab', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-tab', null, null, $attributes);
    }

    public function addCollapseElement(string $label = '', callable|MForm|string $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = [], bool $accordion = false, bool $parse = false): MForm
    {
        $hideToggleLinks = ($hideToggleLinks) ? 'true' : 'false';
        if (!is_array($attributes)) {
            $attributes = [];
        }
        $attributes = array_merge($attributes, ['data-group-accordion' => (int) $accordion, 'data-group-hide-toggle-links' => $hideToggleLinks, 'data-group-open-collapse' => $openCollapse]);

        return $this->addElement('collapse', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-collapse', null, null, $attributes);
    }

    public function addAccordionElement(string $label = '', callable|MForm|string $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = []): MForm
    {
        return $this->addCollapseElement($label, $form, $openCollapse, $hideToggleLinks, $attributes, true);
    }

    public function addRepeaterElement(float|int|string $id, MForm $form, bool $open = true, bool $confirmDelete = true, array $attributes = [], bool $debug = false): MForm
    {
        $attributes['open'] = $open;
        $attributes['confirm_delete'] = $confirmDelete;
        return $this->addElement('repeater', $id, null, $attributes)
            ->addForm($form, false, $debug)
            ->addElement('close-repeater', $id, null, $attributes);
    }

    public function addInputField(string $typ, float|int|string $id, array $attributes = null, string $defaultValue = null): MForm
    {
        return $this->addElement($typ, $id, null, $attributes, null, null, null, $defaultValue);
    }

    public function addHiddenField(float|int|string $id, string $value = null, array $attributes = null): MForm
    {
        return $this->addElement('hidden', $id, $value, $attributes);
    }

    public function addTextField(float|int|string $id, array $attributes = null, string $defaultValue = null): MForm
    {
        return $this->addInputField('text', $id, $attributes, $defaultValue);
    }

    public function addTextAreaField(float|int|string $id, array $attributes = null, string $defaultValue = null): MForm
    {
        return $this->addInputField('textarea', $id, $attributes, $defaultValue);
    }

    public function addTextReadOnlyField(float|int|string $id, string $value = null, array $attributes = null): MForm
    {
        return $this->addElement('text-readonly', $id, $value, $attributes);
    }

    public function addTextAreaReadOnlyField(float|int|string $id, string $value = null, array $attributes = null): MForm
    {
        return $this->addElement('textarea-readonly', $id, $value, $attributes);
    }

    /**
     * add select option fields
     */
    public function addOptionField(string $typ, $id, array $attributes = null, array $options = null, string $defaultValue = null): MForm
    {
        return $this->addElement($typ, $id, null, $attributes, $options, null, null, $defaultValue);
    }

    public function addSelectField(float|int|string $id, array $options = null, array $attributes = null, int $size = 1, string $defaultValue = null): MForm
    {
        $this->addOptionField('select', $id, $attributes, $options, $defaultValue);
        if ($size > 1) {
            $this->setSize($size);
        }
        return $this;
    }

    public function addMultiSelectField(float|int|string $id, array $options = null, array $attributes = null, int $size = 3, string $defaultValue = null): MForm
    {
        $this->addOptionField('multiselect', $id, $attributes, $options, $defaultValue)
            ->setMultiple()
            ->setSize($size);
        return $this;
    }

    public function addCheckboxField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): MForm
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $defaultValue);
    }

    public function addToggleCheckboxField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): MForm
    {
        if (!is_array($attributes)) {
            $attributes = [];
        }
        $attributes['data-mform-toggle'] = 'toggle';
        return $this->addCheckboxField($id, $options, $attributes, $defaultValue);
    }

    public function addRadioField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): MForm
    {
        return $this->addOptionField('radio', $id, $attributes, $options, $defaultValue);
    }

    /**
     * @example
     * $options = [];
     * for ($i = 1; $i <= 20; $i++) {
     *      $options[$i] = ['img' => "../theme/public/assets/backend/img/l$i.svg", 'label' => "Variant $i"];
     * }
     * $mform->addRadioImgField(4, $options, ['label' => 'Layout Type']);
     */
    public function addRadioImgField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): MForm
    {
        $newOptions = [];
        foreach ($options as $key => $option) {
            if (is_array($option) && isset($option['label']) && isset($option['img'])) {
                $newOptions[$key] = "<img src=\"{$option['img']}\"><span>{$option['label']}</span>";
            }
        }
        $this->addHtml('<div class="mform-inline-img-radios mform-inline-radios">');
        $this->addOptionField('radio', $id, $attributes, $newOptions, $defaultValue);
        $this->addHtml('</div>');
        return $this;
    }

    public function addRadioColorField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): MForm
    {
        $newOptions = [];
        foreach ($options as $key => $option) {
            if (is_array($option) && isset($option['label']) && isset($option['color'])) {
                if ($option['color'] == 'transparent') {
                    $newOptions[$key] = "<span style=\"background: linear-gradient(135deg, rgba(255, 255, 255, 0) 0%, rgba(255, 255, 255, 0) 50%, rgba(255, 0, 0, 1) 50%, rgba(255, 0, 0, 1) 100%);\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"{$option['label']}\"></span>";
                } else {
                    $newOptions[$key] = "<span style=\"background-color:{$option['color']}\" data-toggle=\"tooltip\" data-placement=\"top\" title=\"{$option['label']}\"></span>";
                }
            }
        }
        $this->addHtml('<div class="mform-inline-color-radios mform-inline-radios">');
        $this->addOptionField('radio', $id, $attributes, $newOptions, $defaultValue);
        $this->addHtml('</div>');
        return $this;
    }

    /** TODO
     * public function addToggleRadioField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): MForm
     * {
     * //$parallaxMForm->addRadioField("{$config['parallax_id']}.parallax-sticky-test", ['true' => 'on', 'false' => 'off'], ['label' => $config['label']['parallax_sticky'],'class' => 'btn-group', 'data-toggle'] );
     * //$parallaxMForm->addHtml('<div class="btn-group" data-toggle="buttons">
     * //  <label class="btn btn-default active" >
     * //    <input type="radio" name="colour" id="green" value="green"> Green
     * //  </label>
     * //  <label class="btn btn-default" >
     * //    <input type="radio"  name="colour" id="blue" value="blue"> Blue
     * //  </label>
     * //</div>
     * //');
     * return $this->addOptionField('radio', $id, $attributes, $options, $defaultValue);
     * }
     */

    public function addLinkField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): MForm
    {
        return $this->addElement('link', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addLinklistField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): MForm
    {
        return $this->addElement('linklist', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @internal attributes ['data-intern'=>'enable','data-extern'=>'enable','data-media'=>'enable','data-mailto'=>'enable','data-tel'=>'disable', 'data-extern-link-prefix' => 'https://www.', 'data-link-category' => 14, 'data-media-category' => 1, 'data-media-type' => 'jpg,png'];
     *
     * $ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']]
     * ->addCustomLinkField(1, ['label' => 'custom', 'data-intern'=>'disable', 'data-extern'=>'enable', 'ylink' => $ylink])
     */
    public function addCustomLinkField(float|int|string $id, array $attributes = null, string $defaultValue = null): MForm
    {
        return $this->addElement('custom-link', $id, null, $attributes, null, null, null, $defaultValue);
    }

    public function addMediaField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): MForm
    {
        return $this->addElement('media', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addMedialistField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): MForm
    {
        return $this->addElement('medialist', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addImagelistField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): MForm
    {
        return $this->addElement('imglist', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addInputs(float|int|string|null $id, string $filename, array $inputsConfig = []): ?MForm
    {
        if ($id === null) $id = '';
        $inputsConfig['id'] = $id;
        if (!empty($filename)) {
            if (substr($filename,(strlen($filename) - 1), 1) == '/') $filename = substr($filename, 0, strlen($filename) - 1);
            $basename = pathinfo($filename, PATHINFO_BASENAME);
            if (str_contains($filename, '.php')) {
                $filename = substr($filename, 0, strlen($filename) - 4);
            }
            $file = (file_exists(rex_path::addon('mform/inputs', $filename . '.php'))) ? rex_path::addon('mform/inputs', $filename . '.php') : $filename . '.php';
            if (\rex_addon::exists('mfragment') &&
                rex_addon::get('mfragment')->isAvailable() &&
                file_exists(rex_path::addon('mfragment/inputs', $filename . '.php'))) {
                $file = rex_path::addon('mfragment/inputs', $filename . '.php');
            }
            if (file_exists($file)) {
                include_once $file;
                /** @var MFormInputsInterface $inputs */
                $inputs = new $basename($this, $inputsConfig);
                return $this->addForm($inputs->generateInputsForm());
            }
        }
        return $this;
    }

    public function setLabel(string $label): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'label', $label);
        return $this;
    }

    public function setPlaceholder(string $placeholder): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'placeholder', $placeholder);
        return $this;
    }

    public function setFull(): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'full', true);
        return $this;
    }

    public function setFormItemColClass(string $class): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'item-col-class', $class);
        return $this;
    }

    public function setLabelColClass(string $class): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'label-col-class', $class);
        return $this;
    }

    public function setAttributes(array $attributes): MForm
    {
        MFormAttributeHandler::setAttributes($this->item, $attributes);
        return $this;
    }

    public function setAttribute($name, $value): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, $name, $value);
        return $this;
    }

    public function setDefaultValue(string $value): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'default-value', $value);
        return $this;
    }

    public function setOptions(array $options): MForm
    {
        MFormOptionHandler::setOptions($this->item, $options);
        return $this;
    }

    public function setOption($key, $value): MForm
    {
        MFormOptionHandler::addOption($this->item, $value, $key);
        return $this;
    }

    public function setToggleOptions(array $options): MForm
    {
        MFormOptionHandler::toggleOptions($this->item, $options);
        return $this;
    }

    public function setDisableOptions(array $keys): MForm
    {
        MFormOptionHandler::disableOptions($this->item, $keys);
        return $this;
    }

    public function setDisableOption($key): MForm
    {
        MFormOptionHandler::disableOption($this->item, $key);
        return $this;
    }

    public function setSqlOptions($query): MForm
    {
        MFormOptionHandler::setSqlOptions($this->item, $query);
        return $this;
    }

    public function setMultiple(): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'multiple', 'multiple');
        return $this;
    }

    public function setSize($size): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'size', $size);
        return $this;
    }

    public function setCategory($catId): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'catId', $catId);
        return $this;
    }

    public function setParameters(array $parameter): MForm
    {
        MFormParameterHandler::addParameters($this->item, $parameter);
        return $this;
    }

    public function setParameter($name, $value): MForm
    {
        MFormParameterHandler::addParameter($this->item, $name, $value);
        return $this;
    }

    public function setTooltipInfo(?string $value = null, string $icon = ''): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip-icon', $icon);
        return $this;
    }

    public function setTabIcon(string $icon): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'tab-icon', $icon);
        return $this;
    }

    public function setCollapseInfo(?string $value = null, string $icon = ''): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse-icon', $icon);
        return $this;
    }

    public function pullRight(): MForm
    {
        MFormAttributeHandler::addAttribute($this->item, 'pull-right', 1);
        return $this;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems($items): MForm
    {
        $this->items = $items;
        return $this;
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
