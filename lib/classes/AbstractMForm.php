<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

abstract class AbstractMForm
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
     * AbstractMForm constructor.
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

        if (sizeof($attributes) > 0) {
            $this->setAttributes($attributes);
        }
        if (sizeof($options) > 0) {
            $this->setOptions($options);
        }
        if (sizeof($parameter) > 0) {
            $this->setParameters($parameter);
        }
        if (sizeof($validation) > 0) {
            $this->setValidations($validation);
        }

        return $this;
    }

    /**
     * @param null|string $value
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addHtml($value)
    {
        return $this->addElement('html', NULL, $value);
    }

    /**
     * @param null|string $value
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addHeadline($value)
    {
        return $this->addElement('headline', NULL, $value);
    }

    /**
     * @param null|string $value
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addDescription($value)
    {
        return $this->addElement('description', NULL, $value);
    }

    /**
     * @param null|string $value
     * @param array $attributes
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addFieldset($value = null, $attributes = array())
    {
        return $this->addElement('fieldset', NULL, $value, $attributes);
    }

    /**
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function closeFieldset()
    {
        return $this->addElement('close-fieldset', NULL);
    }

    /**
     * @param null $callable
     * @param array $parameter
     * @author Joachim Doerr
     * TODO bring it to run
     * @return $this
     *//*
    public function callback($callable = NULL, $parameter = array())
    {
        //if ((is_string($callable) === true or is_array($callable) === true) && is_callable($callable, true) === true) {
        //    $intId = $this->count++;
        //    $this->elements[$intId] = array(
        //        'type' => 'callback',
        //        'id' => $intId,
        //        'callable' => $callable,
        //        'parameter' => $parameter
        //    );
        //}
        return $this;
    }*/

    /**
     * @param string $typ
     * @param integer|float $id
     * @param array $attributes
     * @param array $validations
     * @param null $defaultValue
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addCheckboxField($id, $options = array(), $attributes = array(), $validation = array(), $defaultValue = NULL)
    {
        return $this->addOptionField('checkbox', $id, $attributes, $options, $validation, $defaultValue);
    }

    /**
     * add multicheckboxe
     * @param $id
     * @param array $options
     * @param array $attributes
     * @param array $validation
     * @param null $defaultValue
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
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
     * @return AbstractMForm
     * @author Joachim Doerr
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
     * @return AbstractMForm
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
     * @return AbstractMForm
     * @author Joachim Doerr
     */
    public function addMedialistField($id, $parameter = array(), $catId = NULL, $attributes = array())
    {
        return $this->addElement('medialist', $id, NULL, $attributes, array(), $parameter, $catId);
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
