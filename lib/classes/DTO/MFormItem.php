<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MFormItem
{
    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $id;

    /**
     * @var array|string
     */
    public $varId;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $defaultValue;

    /**
     * @var string
     */
    public $mode;

    /**
     * @var integer
     */
    public $size;

    /**
     * @var array
     */
    public $attributes = array();

    /**
     * @var string
     */
    public $class;

    /**
     * @var boolean
     */
    public $defaultClass=true;

    /**
     * @var array
     */
    public $options = array();

    /**
     * @var array
     */
    public $parameter = array();

    /**
     * @var boolean
     */
    public $multiple;

    /**
     * @var string
     */
    public $label;

    /**
     * @var boolean
     */
    public $full;

    /**
     * @var boolean
     */
    public $closeFieldset;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $id
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getVarId()
    {
        return $this->varId;
    }

    /**
     * @param array|string $varId
     * @return $this
     */
    public function setVarId($varId)
    {
        $this->varId = $varId;
        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $defaultValue
     * @return $this
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return $this
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int $size
     * @return $this
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param array $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     * @return $this
     */
    public function setClass($class)
    {
        $this->class = $class;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isDefaultClass()
    {
        return $this->defaultClass;
    }

    /**
     * @param boolean $defaultClass
     * @return $this
     */
    public function setDefaultClass($defaultClass)
    {
        $this->defaultClass = $defaultClass;
        return $this;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @return array
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param array $parameter
     * @return $this
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param boolean $multiple
     * @return $this
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isFull()
    {
        return $this->full;
    }

    /**
     * @param boolean $full
     * @return $this
     */
    public function setFull($full)
    {
        $this->full = $full;
        return $this;
    }

    /**
     * @return boolean
     */
    public function isCloseFieldset()
    {
        return $this->closeFieldset;
    }

    /**
     * @param boolean $closeFieldset
     * @return $this
     */
    public function setCloseFieldset($closeFieldset)
    {
        $this->closeFieldset = $closeFieldset;
        return $this;
    }
}
