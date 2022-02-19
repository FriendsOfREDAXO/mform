<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
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
use MForm\Handler\MFormValidationHandler;
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
    private array $result;

    /**
     * MFormElements constructor.
     * @author Joachim Doerr
     */
    public function __construct()
    {
        if (rex_request('function', 'string') == 'edit') {
            // load rex vars
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
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addElement(string $type, $id = NULL, string $value = NULL, array $attributes = NULL, array $options = NULL, array $parameter = NULL, mixed $catId = NULL, array $validation = NULL, string $defaultValue = NULL): self
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
        if (is_array($validation) && sizeof($validation) > 0) {
            $this->setValidations($validation);
        }

        return $this;
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addHtml(?string $value = NULL): self
    {
        return $this->addElement('html', NULL, $value);
    }

    /**
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addHeadline(?string $value = NULL, array $attributes = NULL): self
    {
        return $this->addElement('headline', NULL, $value, $attributes);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addDescription(?string $value = NULL): self
    {
        return $this->addElement('description', NULL, $value);
    }

    /**
     * @param string $key
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlert(string $key, ?string $value = NULL): self
    {
        return $this->addElement('alert', NULL, $value, ['class' => 'alert-' . $key]);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertInfo(?string $value = NULL): self
    {
        return $this->addAlert('info', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertWarning(?string $value = NULL): self
    {
        return $this->addAlert('warning', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertDanger(?string $value = NULL): self
    {
        return $this->addAlert('danger', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertError(?string $value = NULL): self
    {
        return $this->addAlert('error', $value);
    }

    /**
     * @param string|null $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlertSuccess(?string $value = NULL): self
    {
        return $this->addAlert('success', $value);
    }

    /**
     * @param string|null $value
     * @param string $icon
     * @return $this
     * @author Joachim Doerr
     */
    public function addTooltipInfo(?string $value = NULL, string $icon = ''): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip-icon', $icon);
        return $this;
    }

    /**
     * @param string|null $value
     * @param string $icon
     * @return $this
     * @author Joachim Doerr
     */
    public function addCollapseInfo(?string $value = NULL, string $icon = ''): self
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse-icon', $icon);
        return $this;
    }

    /**
     * @param callable|string|Mform|null $form
     * @return $this
     * @author Joachim Doerr
     */
    public function addForm($form = NULL): self
    {
        if (!$form instanceof MForm && is_callable($form)) {
            $form = $form();
        }
        $form = ($form instanceof MForm) ? $form->show() : $form;
        return $this->addHtml($form);
    }

    /**
     * @param string|null $value
     * @param callable|string|Mform|null $form
     * @param array|null $attributes
     * @author Joachim Doerr
     */
    public function addFieldsetField(string $value = NULL, $form = NULL, array $attributes = NULL): self
    {
        return $this->addElement('fieldset', NULL, $value, $attributes)
            ->addForm($form)
            ->addElement('close-fieldset', NULL, NULL, $attributes);
    }

    /**
     * @param string|null $value
     * @param callable|string|Mform|null $form
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTabField(string $value = NULL, $form = NULL, array $attributes = NULL): self
    {
        return $this->addElement('tab', NULL, $value, $attributes)
            ->addForm($form)
            ->addElement('close-tab', NULL, NULL, $attributes);
    }

    /**
     * @param string|null $value
     * @param callable|string|Mform|null $form
     * @param array|null $attributes
     * @param bool $accordion
     * @param bool $hideToggleLinks
     * @param int $openCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function addCollapseField(string $value = NULL, $form = NULL, array $attributes = NULL, bool $accordion = false, bool $hideToggleLinks = false, int $openCollapse = 0): self
    {
        $hideToggleLinks = ($hideToggleLinks) ? 'true' : 'false';
        if (!is_array($attributes)) $attributes = [];
        $attributes = array_merge($attributes, array('data-group-accordion' => (int)$accordion, 'data-group-hide-toggle-links' => $hideToggleLinks, 'data-group-open-collapse' => $openCollapse));

        return $this->addElement('collapse', NULL, $value, $attributes)
            ->addForm($form)
            ->addElement('close-collapse', NULL, NULL, $attributes);
    }

    /**
     * @param string|null $value
     * @param callable|string|Mform|null $form
     * @param array|null $attributes
     * @param bool $hideToggleLinks
     * @param int $openCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function addAccordionField(string $value = NULL, $form = NULL, array $attributes = NULL, bool $hideToggleLinks = false, int $openCollapse = 0): self
    {
        return $this->addCollapseField($value, $form, $attributes, true, $hideToggleLinks, $openCollapse);
    }

    /**
     * @param string $typ
     * @param float|int|string $id
     * @param array|null $attributes
     * @param array|null $validations
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addInputField(string $typ, $id, array $attributes = NULL, array $validations = NULL, string $defaultValue = NULL): self
    {
        return $this->addElement($typ, $id, NULL, $attributes, NULL, NULL, NULL, $validations, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addHiddenField($id, string $value = NULL, array $attributes = NULL): self
    {
        return $this->addElement('hidden', $id, $value, $attributes);
    }

    /**
     * @param float|int|string $id
     * @param array|null $attributes
     * @param array|null $validations
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextField($id, array $attributes = NULL, array $validations = NULL, string $defaultValue = NULL): self
    {
        return $this->addInputField('text', $id, $attributes, $validations, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $attributes
     * @param array|null $validations
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextAreaField($id, array $attributes = NULL, array $validations = NULL, string $defaultValue = NULL): self
    {
        return $this->addInputField('textarea', $id, $attributes, $validations, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param string|null $value
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextReadOnlyField($id, string $value = NULL, array $attributes = NULL): self
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
    public function addTextAreaReadOnlyField($id, string $value = NULL, array $attributes = NULL): self
    {
        return $this->addElement('textarea-readonly', $id, $value, $attributes);
    }

    /**
     * add select fields
     * @param string $typ
     * @param float|int|string $id
     * @param array|null $attributes
     * @param array|null $options
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addOptionField(string $typ, $id, array $attributes = NULL, array $options = NULL, array $validation = NULL, string $defaultValue = NULL): self
    {
        return $this->addElement($typ, $id, NULL, $attributes, $options, NULL, NULL, $validation, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param int $size
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addSelectField($id, array $options = NULL, array $attributes = NULL, int $size = 1, array $validation = NULL, string $defaultValue = NULL): self
    {
        $this->addOptionField('select', $id, $attributes, $options, $validation, $defaultValue);
        if ($size > 1) $this->setSize($size);
        return $this;
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param int $size
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addMultiSelectField($id, array $options = NULL, array $attributes = NULL, int $size = 3, array $validation = NULL, string $defaultValue = NULL): self
    {
        $this->addOptionField('multiselect', $id, $attributes, $options, $validation, $defaultValue)
            ->setMultiple()
            ->setSize($size);
        return $this;
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addCheckboxField($id, array $options = NULL, array $attributes = NULL, array $validation = NULL, string $defaultValue = NULL): self
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $validation, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addToggleCheckboxField($id, array $options = NULL, array $attributes = NULL, array $validation = NULL, string $defaultValue = NULL): self
    {
        if (!is_array($attributes)) $attributes = [];
        $attributes['data-mform-toggle'] = 'toggle';
        return $this->addCheckboxField($id, $options, $attributes, $validation, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $options
     * @param array|null $attributes
     * @param array|null $validation
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addRadioField($id, array $options = NULL, array $attributes = NULL, array $validation = NULL, string $defaultValue = NULL): self
    {
        return $this->addOptionField('radio', $id, $attributes, $options, $validation, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addLinkField($id, array $parameter = NULL, $catId = NULL, array $attributes = NULL): self
    {
        return $this->addElement('link', $id, NULL, $attributes, NULL, $parameter, $catId);
    }

    /**
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addLinklistField($id, array $parameter = NULL, $catId = NULL, array $attributes = NULL): self
    {
        return $this->addElement('linklist', $id, NULL, $attributes, NULL, $parameter, $catId);
    }

    /**
     * @param float|int|string $id
     * @param array|null $attributes
     * @param array|null $validations
     * @param string|null $defaultValue
     * @return $this
     * @author Joachim Doerr
     * @internal attributes ['data-intern'=>'enable','data-extern'=>'enable','data-media'=>'enable','data-mailto'=>'enable','data-tel'=>'disable', 'data-extern-link-prefix' => 'https://www.', 'data-link-category' => 14, 'data-media-category' => 1, 'data-media-type' => 'jpg,png'];
     *
     * $ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']]
     * ->addCustomLinkField(1, ['label' => 'custom', 'data-intern'=>'disable', 'data-extern'=>'enable', 'ylink' => $ylink])
     */
    public function addCustomLinkField($id, array $attributes = NULL, array $validations = NULL, string $defaultValue = NULL): self
    {
        return $this->addElement('custom-link', $id, NULL, $attributes, NULL, NULL, NULL, $validations, $defaultValue);
    }

    /**
     * @param float|int|string $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addMediaField($id, array $parameter = NULL, $catId = NULL, array $attributes = NULL): self
    {
        return $this->addElement('media', $id, NULL, $attributes, NULL, $parameter, $catId);
    }

    /**
     * add rex media list field
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addMedialistField($id, array $parameter = NULL, $catId = NULL, array $attributes = NULL): self
    {
        return $this->addElement('medialist', $id, NULL, $attributes, NULL, $parameter, $catId);
    }

    /**
     * add rex media list field
     * @param float|int|string $id
     * @param array|null $parameter
     * @param null $catId
     * @param array|null $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addImagelistField($id, array $parameter = NULL, $catId = NULL, array $attributes = NULL): self
    {
        return $this->addElement('imglist', $id, NULL, $attributes, NULL, $parameter, $catId);
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
        MFormAttributeHandler::addAttribute($this->item, 'form-item-col-class', $class);
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
     * @param array $validations
     * @return $this
     * @author Joachim Doerr
     */
    public function setValidations(array $validations): self
    {
        MFormValidationHandler::setValidations($this->item, $validations);
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
     * @param string $query
     * @return $this
     * @author Joachim Doerr
     */
    public function setSqlOptions(string $query): self
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
     * @param int $size
     * @return $this
     * @author Joachim Doerr
     */
    public function setSize(int $size): self
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
        MFormParameterHandler::setParameters($this->item, $parameter);
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
    protected function getItems(): array
    {
        return $this->items;
    }
}
