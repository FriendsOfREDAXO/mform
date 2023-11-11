<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm;

use MForm;
use MForm\DTO\MFormItem;
use MForm\Handler\MFormAttributeHandler;
use MForm\Handler\MFormElementHandler;
use MForm\Handler\MFormOptionHandler;
use MForm\Handler\MFormParameterHandler;
use MForm\Handler\MFormValueHandler;
use rex_be_controller;

abstract class MFormElements
{
    /**
     * @var MFormItem[]
     */
    private array $items = [];

    private MFormItem $item;

    private array $result = [];

    /**
     * @description this class contains all addFormElement and setElementOptions methods like addTextField, setLabel, addSelectField, setOptions
     */
    public function __construct()
    {
        // TODO refactor for new usage -> check if ready module form edit
        //      add to mform class
        if (
            (rex_request('function', 'string') === 'edit' && rex_request('function', 'string') != 'add')
            || (rex_be_controller::getCurrentPage() === 'content/edit' && rex_request('function', 'string') != 'add')
        ) {    // load rex vars
            $this->result = MFormValueHandler::loadRexVars();
        }
    }

    /**
     * @description method to generate element array - add fields
     */
    public function addElement(string $type, float|int|string $id = null, string $value = null, array $attributes = null, array $options = null, array $parameter = null, int $catId = null, string $defaultValue = null): self
    {
        // remove ,
        if (!is_int($id)) {
            $id = str_replace(',', '.', (string)$id);
        }
        
        // create item element
        $this->item = MFormElementHandler::createElement((sizeof($this->items) + 1), $type, $id);
        $this->items[$this->item->getId()] = $this->item; // add item element to items array

        // execute to set default value and / or loaded value
        MFormValueHandler::decorateItem($this->item, $this->result, $value, $defaultValue);

        $this->setCategory($catId);

        if (is_array($attributes) && sizeof($attributes) > 0) {
            $this->setAttributes($attributes);
        }
        if (is_array($options) && sizeof($options) > 0) {
            $this->setOptions($options);
        }
        if (is_array($parameter) && sizeof($parameter) > 0) {
            $this->setParameters($parameter);
        }

        return $this;
    }

    public function addHtml(?string $html = null): self
    {
        return $this->addElement('html', null, $html);
    }

    public function addHeadline(?string $value = null, array $attributes = null): self
    {
        return $this->addElement('headline', null, $value, $attributes);
    }

    public function addDescription(?string $value = null): self
    {
        return $this->addElement('description', null, $value);
    }

    public function addAlert(string $key, ?string $value = null): self
    {
        return $this->addElement('alert', null, $value, ['class' => 'alert-' . $key]);
    }

    public function addAlertInfo(?string $value = null): self
    {
        return $this->addAlert('info', $value);
    }

    public function addAlertWarning(?string $value = null): self
    {
        return $this->addAlert('warning', $value);
    }

    public function addAlertDanger(?string $value = null): self
    {
        return $this->addAlert('danger', $value);
    }

    public function addAlertError(?string $value = null): self
    {
        return $this->addAlertDanger($value);
    }

    public function addAlertSuccess(?string $value = null): self
    {
        return $this->addAlert('success', $value);
    }

    public function addForm(callable|MForm|string $form = null, bool $parse = false, bool $debug = false): self
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

    public function addFieldsetArea(string $legend = null, $form = null, array $attributes = [], $parse = false): self
    {
        return $this->addElement('fieldset', null, null, array_merge(['legend' => $legend], $attributes))
            ->addForm($form, $parse)
            ->addElement('close-fieldset', null, null, $attributes);
    }

    public function addColumnElement(int $col, $form = null, array $attributes = [], $parse = false): self
    {
        if (!array_key_exists('class', $attributes) || (isset($attributes['class']) && !str_contains($attributes['class'], 'col-'))) {
            $attributes['class'] = "col-sm-$col" . ((isset($attributes['class'])) ? ' ' . $attributes['class'] : '');
        }
        return $this->addElement('column', null, null, $attributes)
            ->addForm($form, $parse)
            ->addElement('close-column', null, null, $attributes);
    }

    public function addInlineElement(string $label = '', $form = null, array $attributes = [], $parse = false): self
    {
        return $this->addElement('inline', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-inline', null, null, $attributes);
    }

    public function addTabElement(string $label = '', $form = null, bool $openTab = false, bool $pullNaviItemRight = false, array $attributes = [], $parse = false): self
    {
        $attributes = array_merge($attributes, array('data-group-open-tab' => $openTab, 'pull-right' => $pullNaviItemRight));
        return $this->addElement('tab', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-tab', null, null, $attributes);
    }

    public function addCollapseElement(string $label = '', callable|MForm|string $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = [], bool $accordion = false, bool $parse = false): self
    {
        $hideToggleLinks = ($hideToggleLinks) ? 'true' : 'false';
        if (!is_array($attributes)) $attributes = [];
        $attributes = array_merge($attributes, array('data-group-accordion' => (int)$accordion, 'data-group-hide-toggle-links' => $hideToggleLinks, 'data-group-open-collapse' => $openCollapse));

        return $this->addElement('collapse', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-collapse', null, null, $attributes);
    }

    public function addAccordionElement(string $label = '', callable|MForm|string $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = []): self
    {
        return $this->addCollapseElement($label, $form, $openCollapse, $hideToggleLinks, $attributes, true);
    }

    public function addRepeaterElement(float|int|string $id, MForm $form, array $attributes = [], bool $debug = false): self
    {
        return $this->addElement('repeater', $id, null, $attributes)
            ->addForm($form, false, $debug)
            ->addElement('close-repeater', $id, null, $attributes);
    }

    public function addInputField(string $typ, float|int|string $id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addElement($typ, $id, null, $attributes, null, null, null, $defaultValue);
    }

    public function addHiddenField(float|int|string $id, string $value = null, array $attributes = null): self
    {
        return $this->addElement('hidden', $id, $value, $attributes);
    }

    public function addTextField(float|int|string $id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addInputField('text', $id, $attributes, $defaultValue);
    }

    public function addTextAreaField(float|int|string $id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addInputField('textarea', $id, $attributes, $defaultValue);
    }

    public function addTextReadOnlyField(float|int|string $id, string $value = null, array $attributes = null): self
    {
        return $this->addElement('text-readonly', $id, $value, $attributes);
    }

    public function addTextAreaReadOnlyField(float|int|string $id, string $value = null, array $attributes = null): self
    {
        return $this->addElement('textarea-readonly', $id, $value, $attributes);
    }

    /**
     * add select option fields
     */
    public function addOptionField(string $typ, $id, array $attributes = null, array $options = null, string $defaultValue = null): self
    {
        return $this->addElement($typ, $id, null, $attributes, $options, null, null, $defaultValue);
    }

    public function addSelectField(float|int|string $id, array $options = null, array $attributes = null, int $size = 1, string $defaultValue = null): self
    {
        $this->addOptionField('select', $id, $attributes, $options, $defaultValue);
        if ($size > 1) $this->setSize($size);
        return $this;
    }

    public function addMultiSelectField(float|int|string $id, array $options = null, array $attributes = null, int $size = 3, string $defaultValue = null): self
    {
        $this->addOptionField('multiselect', $id, $attributes, $options, $defaultValue)
            ->setMultiple()
            ->setSize($size);
        return $this;
    }

    public function addCheckboxField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $defaultValue);
    }

    public function addToggleCheckboxField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): self
    {
        if (!is_array($attributes)) $attributes = [];
        $attributes['data-mform-toggle'] = 'toggle';
        return $this->addCheckboxField($id, $options, $attributes, $defaultValue);
    }

    public function addRadioField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addOptionField('radio', $id, $attributes, $options, $defaultValue);
    }

    /** TODO
     * public function addToggleRadioField(float|int|string $id, array $options = null, array $attributes = null, string $defaultValue = null): self
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

    public function addLinkField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('link', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addLinklistField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('linklist', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @internal attributes ['data-intern'=>'enable','data-extern'=>'enable','data-media'=>'enable','data-mailto'=>'enable','data-tel'=>'disable', 'data-extern-link-prefix' => 'https://www.', 'data-link-category' => 14, 'data-media-category' => 1, 'data-media-type' => 'jpg,png'];
     *
     * $ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']]
     * ->addCustomLinkField(1, ['label' => 'custom', 'data-intern'=>'disable', 'data-extern'=>'enable', 'ylink' => $ylink])
     */
    public function addCustomLinkField(float|int|string $id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addElement('custom-link', $id, null, $attributes, null, null, null, $defaultValue);
    }

    public function addMediaField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('media', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addMedialistField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('medialist', $id, null, $attributes, null, $parameter, $catId);
    }

    public function addImagelistField(float|int|string $id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('imglist', $id, null, $attributes, null, $parameter, $catId);
    }

    public function setLabel(string $label): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'label', $label);
        return $this;
    }

    public function setPlaceholder(string $placeholder): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'placeholder', $placeholder);
        return $this;
    }

    public function setFull(): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'full', true);
        return $this;
    }

    public function setFormItemColClass(string $class): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'item-col-class', $class);
        return $this;
    }

    public function setLabelColClass(string $class): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'label-col-class', $class);
        return $this;
    }

    public function setAttributes(array $attributes): self
    {
        MFormAttributeHandler::setAttributes($this->item, $attributes);
        return $this;
    }

    public function setAttribute($name, $value): self
    {
        MFormAttributeHandler::addAttribute($this->item, $name, $value);
        return $this;
    }

    public function setDefaultValue(string $value): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'default-value', $value);
        return $this;
    }

    public function setOptions(array $options): self
    {
        MFormOptionHandler::setOptions($this->item, $options);
        return $this;
    }

    public function setOption($key, $value): self
    {
        MFormOptionHandler::addOption($this->item, $value, $key);
        return $this;
    }

    public function setToggleOptions(array $options): self
    {
        MFormOptionHandler::toggleOptions($this->item, $options);
        return $this;
    }

    public function setDisableOptions(array $keys): self
    {
        MFormOptionHandler::disableOptions($this->item, $keys);
        return $this;
    }

    public function setDisableOption($key): self
    {
        MFormOptionHandler::disableOption($this->item, $key);
        return $this;
    }

    public function setSqlOptions($query): self
    {
        MFormOptionHandler::setSqlOptions($this->item, $query);
        return $this;
    }

    public function setMultiple(): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'multiple', 'multiple');
        return $this;
    }

    public function setSize($size): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'size', $size);
        return $this;
    }

    public function setCategory($catId): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'catId', $catId);
        return $this;
    }

    public function setParameters(array $parameter): self
    {
        MFormParameterHandler::addParameters($this->item, $parameter);
        return $this;
    }

    public function setParameter($name, $value): self
    {
        MFormParameterHandler::addParameter($this->item, $name, $value);
        return $this;
    }

    public function setTooltipInfo(?string $value = null, string $icon = ''): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip-icon', $icon);
        return $this;
    }

    public function setTabIcon(string $icon): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'tab-icon', $icon);
        return $this;
    }

    public function setCollapseInfo(?string $value = null, string $icon = ''): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse-icon', $icon);
        return $this;
    }

    public function pullRight(): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'pull-right', 1);
        return $this;
    }

    /**
     * @return MFormItem[]
     * @author Joachim Doerr
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems($items): self
    {
        $this->items = $items;
        return $this;
    }

    public function getResult(): array
    {
        return $this->result;
    }
}
