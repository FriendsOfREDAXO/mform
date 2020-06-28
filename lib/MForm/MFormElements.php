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
    private $items = array();

    /**
     * @var MFormItem
     */
    private $item;

    /**
     * @var array
     */
    private $result;

    /**
     * MFormElements constructor.
     * @author Joachim Doerr
     */
    public function __construct()
    {
        if (!$this->result && rex_request('function', 'string') == 'edit') {
            // load rex vars
            $this->result = MFormValueHandler::loadRexVars();
        }
    }

    /**
     * generate element array - add fields
     * @param string $type
     * @param integer|float $id
     * @param null|string $value
     * @param array $attributes
     * @param array $options
     * @param array $parameter
     * @param null $catId
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addElement($type, $id = NULL, $value = NULL, $attributes = array(), $options = array(), $parameter = array(), $catId = NULL, $validation = array(), $defaultValue = NULL)
    {
        // remove ,
        $id = str_replace(',','.',$id);

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
     * @param null|string $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addHtml($value)
    {
        return $this->addElement('html', NULL, $value);
    }

    /**
     * @param null|string $value
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addHeadline($value, $attributes = array())
    {
        return $this->addElement('headline', NULL, $value, $attributes);
    }

    /**
     * @param null|string $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addDescription($value)
    {
        return $this->addElement('description', NULL, $value);
    }

    /**
     * @param string $key
     * @param null|string $value
     * @return $this
     * @author Joachim Doerr
     */
    public function addAlert($key, $value)
    {
        return $this->addElement('alert', NULL, $value, array('class'=>'alert-'.$key));
    }

    /**
     * @param $value
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addAlertInfo($value)
    {
        return $this->addAlert('info', $value);
    }

    /**
     * @param $value
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addAlertWarning($value)
    {
        return $this->addAlert('warning', $value);
    }

    /**
     * @param $value
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addAlertDanger($value)
    {
        return $this->addAlert('danger', $value);
    }

    /**
     * @param $value
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addAlertSuccess($value)
    {
        return $this->addAlert('success', $value);
    }

    /**
     * @param $value
     * @param $icon
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addTooltipInfo($value, $icon = '')
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-tooltip-icon', $icon);
        return $this;
    }

    /**
     * @param $value
     * @param $icon
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addCollapseInfo($value, $icon = '')
    {
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse', $value);
        MFormAttributeHandler::addAttribute($this->item, 'info-collapse-icon', $icon);
        return $this;
    }

    /**
     * @param null|string $value
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addFieldset($value = null, $attributes = array())
    {
        return $this->addElement('fieldset', NULL, $value, $attributes);
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function closeFieldset()
    {
        return $this->addElement('close-fieldset', NULL);
    }

    /**
     * @param null|string $value
     * @param array $attributes
     * @deprecated this method will be removed in MForm 7 please use MForm::factory()->addTabField();
     * @return $this
     * @author Joachim Doerr
     */
    public function addTab($value = null, $attributes = array())
    {
        return $this->addElement('tab', NULL, $value, $attributes);
    }

    /**
     * @param bool $tabGroupClose
     * @deprecated this method will be removed in MForm 7 please use MForm::factory()->addTabField();
     * @return $this
     * @author Joachim Doerr
     */
    public function closeTab($tabGroupClose = false)
    {
        $attributes = array('data-close-group-tab' => (int) $tabGroupClose);
        return $this->addElement('close-tab', NULL, NULL, $attributes);
    }

    /**
     * @param null|string $value
     * @param array $attributes
     * @param bool $accordion
     * @param bool $hideToggleLinks
     * @param int $openCollapse
     * @deprecated this method will be removed in MForm 7 please use MForm::factory()->addCollapseField();
     * @return $this
     * @author Joachim Doerr
     */
    public function addCollapse($value = null, $attributes = array(), $accordion = false, $hideToggleLinks = false, $openCollapse = 0)
    {
        $hideToggleLinks = ($hideToggleLinks) ? 'true' : 'false';
        $attributes = array_merge($attributes, array('data-group-accordion' => (int) $accordion, 'data-group-hide-toggle-links' => $hideToggleLinks, 'data-group-open-collapse' => $openCollapse));
        return $this->addElement('collapse', NULL, $value, $attributes);
    }

    /**
     * @param bool $collapseGroupClose
     * @deprecated this method will be removed in MForm 7 please use MForm::factory()->addCollapseField();
     * @return $this
     * @author Joachim Doerr
     */
    public function closeCollapse($collapseGroupClose = false)
    {
        $attributes = array('data-close-group-collapse' => (int) $collapseGroupClose);
        return $this->addElement('close-collapse', NULL, NULL, $attributes);
    }

    /**
     * @param null|string $value
     * @param bool $hideToggleLinks
     * @param int $openCollapse
     * @deprecated this method will be removed in MForm 7 please use MForm::factory()->addAccordionField();
     * @return $this
     * @author Joachim Doerr
     */
    public function addAccordion($value = null, $hideToggleLinks = false, $openCollapse = 0)
    {
        return $this->addCollapse($value, array(), true, $hideToggleLinks, $openCollapse);
    }

    /**
     * @param bool $accordionGroupClose
     * @deprecated this method will be removed in MForm 7 please use MForm::factory()->addAccordionField();
     * @return $this
     * @author Joachim Doerr
     */
    public function closeAccordion($accordionGroupClose = false)
    {
        return $this->closeCollapse($accordionGroupClose);
    }

    /**
     * @param null $form
     * @return MFormElements
     * @author Joachim Doerr
     */
    public function addForm($form = null)
    {
        $form = ($form instanceof MForm) ? $form->show() : $form;
        return $this->addHtml($form);
    }

    /**
     * @param null $value
     * @param null $form
     * @param array $attributes
     * @author Joachim Doerr
     */
    public function addFieldsetField($value = null, $form = null, $attributes = array())
    {
        $this->addElement('fieldset', NULL, $value, $attributes)
            ->addForm($form)
            ->addElement('close-fieldset', NULL, NULL, $attributes);
    }

    /**
     * @param null|string $value
     * @param null|MForm|string $form
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTabField($value = null, $form = null, $attributes = array())
    {
        $this->addElement('tab', NULL, $value, $attributes)
            ->addForm($form)
            ->addElement('close-tab', NULL, NULL, $attributes);

        return $this;
    }

    /**
     * @param null|string $value
     * @param null|MForm|string $form
     * @param array $attributes
     * @param bool $accordion
     * @param bool $hideToggleLinks
     * @param int $openCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function addCollapseField($value = null, $form = null, $attributes = array(), $accordion = false, $hideToggleLinks = false, $openCollapse = 0)
    {
        $hideToggleLinks = ($hideToggleLinks) ? 'true' : 'false';
        $attributes = array_merge($attributes, array('data-group-accordion' => (int) $accordion, 'data-group-hide-toggle-links' => $hideToggleLinks, 'data-group-open-collapse' => $openCollapse));

        $this->addElement('collapse', NULL, $value, $attributes)
            ->addForm($form)
            ->addElement('close-collapse', NULL, NULL, $attributes);

        return $this;
    }

    /**
     * @param null|string $value
     * @param null|MForm|string $form
     * @param array $attributes
     * @param bool $hideToggleLinks
     * @param int $openCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function addAccordionField($value = null, $form = null, $attributes = array(), $hideToggleLinks = false, $openCollapse = 0)
    {
        return $this->addCollapseField($value, $form, $attributes, true, $hideToggleLinks, $openCollapse);
    }

    /**
     * @param string $typ
     * @param integer|float $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addInputField($typ, $id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addElement($typ, $id, NULL, $attributes, NULL, NULL, NULL, $validations, $defaultValue);
    }

    /**
     * @param $id
     * @param null|string $value
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addHiddenField($id, $value = NULL, $attributes = array())
    {
        return $this->addElement('hidden', $id, $value, $attributes, NULL, NULL, NULL, array(), array());
    }

    /**
     * @param float $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addInputField('text', $id, $attributes, $validations, $defaultValue);
    }

    /**
     * @param integer|float $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextAreaField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addInputField('textarea', $id, $attributes, $validations, $defaultValue);
    }

    /**
     * @param $id
     * @param null $value
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextReadOnlyField($id, $value = NULL, $attributes = array())
    {
        return $this->addElement('text-readonly', $id, $value, $attributes, NULL, NULL, NULL, array(), array());
    }

    /**
     * @param $id
     * @param null $value
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addTextAreaReadOnlyField($id, $value = NULL, $attributes = array())
    {
        return $this->addElement('textarea-readonly', $id, $value, $attributes, NULL, NULL, NULL, array(), array());
    }

    /**
     * add select fields
     * @param $typ
     * @param $id
     * @param array $attributes
     * @param array $options
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addOptionField($typ, $id, $attributes = array(), $options = array(), $validation = array(), $defaultValue = NULL)
    {
        return $this->addElement($typ, $id, NULL, $attributes, $options, NULL, NULL, $validation, $defaultValue);
    }

    /**
     * @param float $id
     * @param array $options
     * @param array $attributes
     * @param int $size
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addSelectField($id, $options = array(), $attributes = array(), $size = 1, $validation = array(), $defaultValue = NULL)
    {
        $this->addOptionField('select', $id, $attributes, $options, $validation, $defaultValue);
        if ($size > 1) $this->setSize($size);
        return $this;
    }

    /**
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param int $size
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addMultiSelectField($id, $options = array(), $attributes = array(), $size = 3, $validation = array(), $defaultValue = NULL)
    {
        $this->addOptionField('multiselect', $id, $attributes, $options, $validation, $defaultValue)
            ->setMultiple()
            ->setSize($size);
        return $this;
    }

    /**
     * add checkboxe
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addCheckboxField($id, $options = array(), $attributes = array(), $validation = array(), $defaultValue = NULL)
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $validation, $defaultValue);
    }

    /**
     * add checkboxe
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addToggleCheckboxField($id, $options = array(), $attributes = array(), $validation = array(), $defaultValue = NULL)
    {
        $attributes['data-mform-toggle'] = 'toggle';
        return $this->addCheckboxField($id, $options, $attributes, $validation, $defaultValue);
    }

    /**
     * add multicheckboxe
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */ /*
    // TODO bring it to live
    public function addMultiCheckboxField($id, $options = array(), $attributes = array(), $validation = array(), $defaultValue = NULL)
    {
        return $this->addOptionField('multicheckbox', $id, $attributes, $options, $validation, $defaultValue)
            ->setMultiple();
    } */

    /**
     * add radiobutton
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param array $validation
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     */
    public function addRadioField($id, $options = array(), $attributes = array(), $validation = array(), $defaultValue = NULL)
    {
        return $this->addOptionField('radio', $id, $attributes, $options, $validation, $defaultValue);
    }

    /**
     * add rex link field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addLinkField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('link', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add rex link list field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addLinklistField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('linklist', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add special link feld
     * @param $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return $this
     * @author Joachim Doerr
     * @internal attributes array('data-intern'=>'disable','data-extern'=>'disable','data-media'=>'disable','data-mailto'=>'enable','data-tel'=>'enable');
     */
    public function addCustomLinkField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
    {
        return $this->addElement('custom-link', $id, NULL, $attributes, array(), NULL, NULL, $validations, $defaultValue);
    }

    /**
     * add rex media field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addMediaField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('media', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add rex media list field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addMedialistField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('medialist', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * add rex media list field
     * @param $id
     * @param array $parameter
     * @param null $catId
     * @param array $attributes
     * @return $this
     * @author Joachim Doerr
     */
    public function addImagelistField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('imglist', $id, NULL, $attributes, array(), $parameter, $catId);
    }

    /**
     * @param $label
     * @author Joachim Doerr
     * @return $this
     */
    public function setLabel($label)
    {
        MFormAttributeHandler::addAttribute($this->item, 'label', $label);
        return $this;
    }

    /**
     * @param $placeholder
     * @author Joachim Doerr
     * @return $this
     */
    public function setPlaceholder($placeholder)
    {
        MFormAttributeHandler::addAttribute($this->item, 'placeholder', $placeholder);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setFull()
    {
        MFormAttributeHandler::addAttribute($this->item, 'full', true);
        return $this;
    }

    /**
     * @param $class
     * @return $this
     * @author Joachim Doerr
     */
    public function setFormItemColClass($class)
    {
        MFormAttributeHandler::addAttribute($this->item, 'form-item-col-class', $class);
        return $this;
    }

    /**
     * @param $class
     * @return $this
     * @author Joachim Doerr
     */
    public function setLabelColClass($class)
    {
        MFormAttributeHandler::addAttribute($this->item, 'label-col-class', $class);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setToggle()
    {
        MFormAttributeHandler::addAttribute($this->item, 'data-mform-toggle', 'toggle');
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     * @deprecated This method will be removed in v5.2.0 use addAttribute
     * @return $this
     */
    public function setAttribute($name, $value)
    {
        MFormAttributeHandler::addAttribute($this->item, $name, $value);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     * @return $this
     */
    public function addAttribute($name, $value)
    {
        MFormAttributeHandler::addAttribute($this->item, $name, $value);
        return $this;
    }

    /**
     * @param $attributes
     * @author Joachim Doerr
     * @return $this
     */
    public function setAttributes($attributes)
    {
        MFormAttributeHandler::setAttributes($this->item, $attributes);
        return $this;
    }

    /**
     * add default validation
     * @param $key
     * @param $value
     * @author Joachim Doerr
     * @deprecated This method will be removed in v5.2.0 use addValidation
     * @return $this
     */
    public function setValidation($key, $value = null)
    {
        MFormValidationHandler::addValidation($this->item, $key, $value);
        return $this;
    }


    /**
     * add default validation
     * @param $key
     * @param $value
     * @author Joachim Doerr
     * @return $this
     */
    public function addValidation($key, $value = null)
    {
        MFormValidationHandler::addValidation($this->item, $key, $value);
        return $this;
    }
    /**
     * @param $validations
     * @author Joachim Doerr
     * @return $this
     */
    public function setValidations($validations)
    {
        MFormValidationHandler::setValidations($this->item, $validations);
        return $this;
    }

    /**
     * @param $value
     * @author Joachim Doerr
     * @return $this
     */
    public function setDefaultValue($value)
    {
        MFormAttributeHandler::addAttribute($this->item, 'default-value', $value);
        return $this;
    }

    /**
     * @param $value
     * @param $key
     * @author Joachim Doerr
     * @return $this
     */
    public function addOption($value, $key)
    {
        MFormOptionHandler::addOption($this->item, $value, $key);
        return $this;
    }

    /**
     * @param $key
     * @author Joachim Doerr
     * @return $this
     */
    public function disableOption($key)
    {
        MFormOptionHandler::disableOption($this->item, $key);
        return $this;
    }

    /**
     * @param $options
     * @return $this
     * @author Joachim Doerr
     */
    public function setOptions($options)
    {
        MFormOptionHandler::setOptions($this->item, $options);
        return $this;
    }

    /**
     * @param $keys
     * @return $this
     * @author Joachim Doerr
     */
    public function disableOptions($keys)
    {
        MFormOptionHandler::disableOptions($this->item, $keys);
        return $this;
    }

    /**
     * @param $query
     * @author Joachim Doerr
     * @return $this
     */
    public function setSqlOptions($query)
    {
        MFormOptionHandler::setSqlOptions($this->item, $query);
        return $this;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setMultiple()
    {
        MFormAttributeHandler::addAttribute($this->item, 'multiple', 'multiple');
        return $this;
    }

    /**
     * @param $size
     * @author Joachim Doerr
     * @return $this
     */
    public function setSize($size)
    {
        MFormAttributeHandler::addAttribute($this->item, 'size', $size);
        return $this;
    }

    /**
     * set category and parameter
     * @param $catId
     * @author Joachim Doerr
     * @return $this
     */
    public function setCategory($catId)
    {
        MFormAttributeHandler::addAttribute($this->item, 'catId', $catId);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     * @deprecated This method will be removed in v5.2.0 use addParameter
     * @return $this
     */
    public function setParameter($name, $value)
    {
        MFormParameterHandler::addParameter($this->item, $name, $value);
        return $this;
    }

    /**
     * @param $name
     * @param $value
     * @author Joachim Doerr
     * @return $this
     */
    public function addParameter($name, $value)
    {
        MFormParameterHandler::addParameter($this->item, $name, $value);
        return $this;
    }

    /**
     * @param $parameter
     * @author Joachim Doerr
     * @return $this
     */
    public function setParameters($parameter)
    {
        MFormParameterHandler::setParameters($this->item, $parameter);
        return $this;
    }

    /**
     * @param string $icon
     * @author Joachim Doerr
     * @return $this
     */
    public function setTabIcon($icon)
    {
        MFormAttributeHandler::addAttribute($this->item, 'tab-icon', $icon);
        return $this;
    }

    /**
     * @author Joachim Doerr
     * @return $this
     */
    public function pullRight()
    {
        MFormAttributeHandler::addAttribute($this->item, 'pull-right', 1);
        return $this;
    }

    /**
     * check serialize
     * @param $string
     * @return bool
     * @author Joachim Doerr
     * TODO check for what is it good
     */
    public static function isSerial($string)
    {
        return (@unserialize($string) !== false);
    }

    /**
     * @return MFormItem[]
     * @author Joachim Doerr
     */
    protected function getItems()
    {
        return $this->items;
    }
}
