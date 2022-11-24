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

class MFormElements
{
    /**
     * @var MFormItem[]
     */
    private array $items = [];

    /**
     * @var MFormItem
     */
    private MFormItem $item;

    /**
     * @var array
     */
    private array $result = [];

    /**
     * MFormElements constructor.
     * @author Joachim Doerr
     */
    public function __construct()
    {
     if ((rex_request('function', 'string') === 'edit' && rex_request('function', 'string') != 'add') || (\rex_be_controller::getCurrentPage() === 'content/edit' && rex_request('function', 'string') != 'add')) {            // load rex vars
            $this->result = MFormValueHandler::loadRexVars();
        }
    }

    /**
     * generate element array - add fields
     * @param string $type
     * @param float|int|string|null $id
     * @param string|null $value
     * @param array|null $attributes
     * @param array|null $options
     * @param array|null $parameter
     * @param mixed|null $catId
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addElement(string $type, $id = null, string $value = null, array $attributes = null, array $options = null, array $parameter = null, $catId = null, string $defaultValue = null): self
    {
        // remove ,
        $id = str_replace(',', '.', $id);

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

    /**
     * @param string|null $html
     * @return $this
     * @author Joachim Doerr
     */
    public function addHtml(?string $html = null): self
    {
        return $this->addElement('html', null, $html);
    }

    /**
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addHeadline(?string $value = null, array $attributes = null): self
    {
        return $this->addElement('headline', null, $value, $attributes);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addDescription(?string $value = null): self
    {
        return $this->addElement('description', null, $value);
    }

    /**
     * @param string $key
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlert(string $key, ?string $value = null): self
    {
        return $this->addElement('alert', null, $value, ['class' => 'alert-' . $key]);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertInfo(?string $value = null): self
    {
        return $this->addAlert('info', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertWarning(?string $value = null): self
    {
        return $this->addAlert('warning', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertDanger(?string $value = null): self
    {
        return $this->addAlert('danger', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertError(?string $value = null): self
    {
        return $this->addAlertDanger($value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertSuccess(?string $value = null): self
    {
        return $this->addAlert('success', $value);
    }

    /**
     * @param callable|string|Mform|null $form
     * @return $this
     * @author Joachim Doerr
     */
    public function addForm($form = null, $parse = true): self
    {
        if (!$form instanceof MForm && is_callable($form)) {
            $form = $form();
        }
        if ($form instanceof MForm) {
            if (!$parse) {
                $this->items[] = $form;
                return $this;
            }
            $form = $form->show();
        }
        return $this->addHtml($form);
    }

    /**
     * @param string|null $legend
     * @param callable|string|Mform|null $form
     * @param array|null $attributes
     * @author Joachim Doerr
     */
    public function addFieldsetArea(string $legend = null, $form = null, array $attributes = [], $parse = true): self
    {
        return $this->addElement('fieldset', null, null, array_merge(['legend' => $legend], $attributes))
            ->addForm($form, $parse)
            ->addElement('close-fieldset', null, null, $attributes);
    }

    /**
     * @param int $col
     * @param null $form
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addColumnElement(int $col, $form = null, array $attributes = [], $parse = true): self
    {
        if (!array_key_exists('class', $attributes) || (isset($attributes['class']) && !str_contains($attributes['class'], 'col-'))) {
            $attributes['class'] = "col-sm-$col" . ((isset($attributes['class'])) ? ' ' . $attributes['class'] : '');
        }
        return $this->addElement('column', null, null, $attributes)
            ->addForm($form, $parse)
            ->addElement('close-column', null, null, $attributes);
    }

    /**
     * @param string|null $label
     * @param null $form
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addInlineElement(string $label = '', $form = null, array $attributes = [], $parse = true): self
    {
        if ($form instanceof MForm) $form->setInline(true);
        return $this->addElement('inline', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-inline', null, null, $attributes);
    }

    /**
     * @param string|null $label
     * @param callable|string|Mform|null $form
     * @param bool $openTab
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTabElement(string $label = '', $form = null, bool $openTab = false, bool $pullNaviItemRight = false, array $attributes = [], $parse = true): self
    {
        $attributes = array_merge($attributes, array('data-group-open-tab' => $openTab, 'pull-right' => $pullNaviItemRight));
        return $this->addElement('tab', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-tab', null, null, $attributes);
    }

    /**
     * @param string|null $label
     * @param callable|string|Mform|null $form
     * @param array|null $attributes
     * @param bool $accordion
     * @param bool $hideToggleLinks
     * @param bool $openCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function addCollapseElement(string $label = '', $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = [], bool $accordion = false, $parse = true): self
    {
        $hideToggleLinks = ($hideToggleLinks) ? 'true' : 'false';
        if (!is_array($attributes)) $attributes = [];
        $attributes = array_merge($attributes, array('data-group-accordion' => (int)$accordion, 'data-group-hide-toggle-links' => $hideToggleLinks, 'data-group-open-collapse' => $openCollapse));

        return $this->addElement('collapse', null, null, $attributes)
            ->setLabel($label)
            ->addForm($form, $parse)
            ->addElement('close-collapse', null, null, $attributes);
    }

    /**
     * @param string|null $label
     * @param callable|string|Mform|null $form
     * @param array|null $attributes
     * @param bool $hideToggleLinks
     * @param bool $openCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function addAccordionElement(string $label = '', $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = []): self
    {
        return $this->addCollapseElement($label, $form, $openCollapse, $hideToggleLinks, $attributes, true);
    }

    /**
     * @param string $typ
     * @param float|int|string $id
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addInputField(string $typ, $id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addElement($typ, $id, null, $attributes, null, null, null, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addHiddenField($id, string $value = null, array $attributes = null): self
    {
        return $this->addElement('hidden', $id, $value, $attributes);
    }

    /**
     * @param float|int|string $id
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextField($id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addInputField('text', $id, $attributes, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextAreaField($id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addInputField('textarea', $id, $attributes, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextReadOnlyField($id, string $value = null, array $attributes = null): self
    {
        return $this->addElement('text-readonly', $id, $value, $attributes);
    }

    /**
     * @param float|int|string $id
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextAreaReadOnlyField($id, string $value = null, array $attributes = null): self
    {
        return $this->addElement('textarea-readonly', $id, $value, $attributes);
    }

    /**
     * add select fields
     * @param string $typ
     * @param float|int|string $id
     * @param array|null $attributes
     * @param array|null $options
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addOptionField(string $typ, $id, array $attributes = null, array $options = null, string $defaultValue = null): self
    {
        return $this->addElement($typ, $id, null, $attributes, $options, null, null, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param int $size
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addSelectField($id, array $options = null, array $attributes = null, int $size = 1, string $defaultValue = null): self
    {
        $this->addOptionField('select', $id, $attributes, $options, $defaultValue);
        if ($size > 1) $this->setSize($size);
        return $this;
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param int $size
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addMultiSelectField($id, array $options = null, array $attributes = null, int $size = 3, string $defaultValue = null): self
    {
        $this->addOptionField('multiselect', $id, $attributes, $options, $defaultValue)
            ->setMultiple()
            ->setSize($size);
        return $this;
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addCheckboxField($id, array $options = null, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addToggleCheckboxField($id, array $options = null, array $attributes = null, string $defaultValue = null): self
    {
        if (!is_array($attributes)) $attributes = [];
        $attributes['data-mform-toggle'] = 'toggle';
        return $this->addCheckboxField($id, $options, $attributes, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addRadioField($id, array $options = null, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addOptionField('radio', $id, $attributes, $options, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addLinkField($id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('link', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addLinklistField($id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('linklist', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @param float|int|string $id
     * @param array|null $attributes
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     * @internal attributes ['data-intern'=>'enable','data-extern'=>'enable','data-media'=>'enable','data-mailto'=>'enable','data-tel'=>'disable', 'data-extern-link-prefix' => 'https://www.', 'data-link-category' => 14, 'data-media-category' => 1, 'data-media-type' => 'jpg,png'];
     *
     * $ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']]
     * ->addCustomLinkField(1, ['label' => 'custom', 'data-intern'=>'disable', 'data-extern'=>'enable', 'ylink' => $ylink])
     */
    public function addCustomLinkField($id, array $attributes = null, string $defaultValue = null): self
    {
        return $this->addElement('custom-link', $id, null, $attributes, null, null, null, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addMediaField($id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('media', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addMedialistField($id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('medialist', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addImagelistField($id, array $parameter = null, $catId = null, array $attributes = null): self
    {
        return $this->addElement('imglist', $id, null, $attributes, null, $parameter, $catId);
    }

    /**
     * @param string $label
     * @return $this
     * @author Joachim Doerr
     */
    public function setLabel(string $label): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'label', $label);
        return $this;
    }

    /**
     * @param string $placeholder
     * @return $this
     * @author Joachim Doerr
     */
    public function setPlaceholder(string $placeholder): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'placeholder', $placeholder);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setFull(): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'full', true);
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     * @author Joachim Doerr
     */
    public function setFormItemColClass(string $class): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'item-col-class', $class);
        return $this;
    }

    /**
     * @param string $class
     * @return $this
     * @author Joachim Doerr
     */
    public function setLabelColClass(string $class): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'label-col-class', $class);
        return $this;
    }

    /**
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function setAttributes(array $attributes): self
    {
        MFormAttributeHandler::setAttributes($this->item, $attributes);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     * @return $this
     */
    public function setAttribute($name, $value): self
    {
        MFormAttributeHandler::addAttribute($this->item, $name, $value);
        return $this;
    }

    /**
     * @param string $value
     * @return $this
     * @author Joachim Doerr
     */
    public function setDefaultValue(string $value): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'default-value', $value);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     * @author Joachim Doerr
     */
    public function setOptions(array $options): self
    {
        MFormOptionHandler::setOptions($this->item, $options);
        return $this;
    }

    /**
     * @param $key
     * @param $value
     * @return $this
     * @author Joachim Doerr
     */
    public function setOption($key, $value): self
    {
        MFormOptionHandler::addOption($this->item, $value, $key);
        return $this;
    }

    /**
     * @param array $options
     * @return $this
     * @author Joachim Doerr
     */
    public function setToggleOptions(array $options): self
    {
        MFormOptionHandler::toggleOptions($this->item, $options);
        return $this;
    }

    /**
     * @param array $keys
     * @return $this
     * @author Joachim Doerr
     */
    public function setDisableOptions(array $keys): self
    {
        MFormOptionHandler::disableOptions($this->item, $keys);
        return $this;
    }

    /**
     * @param $key
     * @author Joachim Doerr
     * @return $this
     */
    public function setDisableOption($key): self
    {
        MFormOptionHandler::disableOption($this->item, $key);
        return $this;
    }

    /**
     * @param $query
     * @return $this
     * @author Joachim Doerr
     */
    public function setSqlOptions($query): self
    {
        MFormOptionHandler::setSqlOptions($this->item, $query);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setMultiple(): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'multiple', 'multiple');
        return $this;
    }

    /**
     * @param $size
     * @return $this
     * @author Joachim Doerr
     */
    public function setSize($size): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'size', $size);
        return $this;
    }

    /**
     * @param $catId
     * @return $this
     * @author Joachim Doerr
     */
    public function setCategory($catId): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'catId', $catId);
        return $this;
    }

    /**
     * @param array $parameter
     * @return $this
     * @author Joachim Doerr
     */
    public function setParameters(array $parameter): self
    {
        MFormParameterHandler::addParameters($this->item, $parameter);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @return $this
     * @author Joachim Doerr
     */
    public function setParameter($name, $value): self
    {
        MFormParameterHandler::addParameter($this->item, $name, $value);
        return $this;
    }

    /**
     * @param string|null $value
     * @param string $icon
     * @return $this
     * @author Joachim Doerr
     */
    public function setTooltipInfo(?string $value = null, string $icon = ''): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip-icon', $icon);
        return $this;
    }

    /**
     * @param string $icon
     * @return $this
     * @author Joachim Doerr
     */
    public function setTabIcon(string $icon): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'tab-icon', $icon);
        return $this;
    }

    /**
     * @param string|null $value
     * @param string $icon
     * @return $this
     * @author Joachim Doerr
     */
    public function setCollapseInfo(?string $value = null, string $icon = ''): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse-icon', $icon);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
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

    /**
     * @param array $items
     * @return $this
     * @author Joachim Doerr
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getResult()
    {
        return $this->result;
    }
}
