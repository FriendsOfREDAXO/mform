<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\DTO;

class MFormItem
{
    /** @var string */
    public $type;

    /** @var string */
    public $id;

    /** @var array|string */
    public $varId;

    /** @var string|array */
    public $value;

    /** @var string */
    public $stringValue;

    /** @var string */
    public $defaultValue = '';

    /** @var string */
    public $mode;

    /** @var int */
    public $size;

    /** @var array */
    public $attributes = [];

    /** @var string */
    public $class = '';

    /** @var bool */
    public $defaultClass = true;

    /** @var array */
    public $options = [];

    /** @var array */
    public $disabledOptions = [];

    /** @var array */
    public $toggleOptions = [];

    /** @var array */
    public $parameter = [];

    /** @var bool */
    public $multiple;

    /** @var string|array */
    public $label;

    /** @var string */
    public $legend;

    /** @var bool */
    public $full;

    /** @var string */
    public $labelColClass;

    /** @var string */
    public $formItemColClass;

    /** @var int */
    public $group;

    /** @var int */
    public $groupCount;

    /** @var string */
    public $groupKey;

    /** @var string */
    public $infoTooltip;

    /** @var string */
    public $infoCollapse;

    /** @var string */
    public $infoTooltipIcon;

    /** @var string */
    public $infoCollapseIcon;

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
     * @return string|array
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
     * @author Joachim Doerr
     */
    public function getStringValue()
    {
        return $this->stringValue;
    }

    /**
     * @param string $stringValue
     * @return $this
     * @author Joachim Doerr
     */
    public function setStringValue($stringValue)
    {
        $this->stringValue = $stringValue;
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
     * @return bool
     */
    public function isDefaultClass()
    {
        return $this->defaultClass;
    }

    /**
     * @param bool $defaultClass
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
     * @author Joachim Doerr
     */
    public function getToggleOptions(): array
    {
        return $this->toggleOptions;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setToggleOptions(array $toggleOptions)
    {
        $this->toggleOptions = $toggleOptions;
        return $this;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getDisabledOptions()
    {
        return $this->disabledOptions;
    }

    /**
     * @param array $disabledOptions
     * @return MFormItem
     * @author Joachim Doerr
     */
    public function setDisabledOptions($disabledOptions)
    {
        $this->disabledOptions = $disabledOptions;
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
     * @return bool
     */
    public function isMultiple()
    {
        return $this->multiple;
    }

    /**
     * @param bool $multiple
     * @return $this
     */
    public function setMultiple($multiple)
    {
        $this->multiple = $multiple;
        return $this;
    }

    /**
     * @return string|array
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string|array $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string|null
     * @author Joachim Doerr
     */
    public function getLegend()
    {
        return $this->legend;
    }

    /**
     * @return $this
     * @author Joachim Doerr
     */
    public function setLegend(string $legend)
    {
        $this->legend = $legend;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFull()
    {
        return $this->full;
    }

    /**
     * @param bool $full
     * @return $this
     */
    public function setFull($full)
    {
        $this->full = $full;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getLabelColClass()
    {
        return $this->labelColClass;
    }

    /**
     * @param string $labelColClass
     * @return $this
     * @author Joachim Doerr
     */
    public function setLabelColClass($labelColClass)
    {
        $this->labelColClass = $labelColClass;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getFormItemColClass()
    {
        return $this->formItemColClass;
    }

    /**
     * @param string $formItemColClass
     * @return $this
     * @author Joachim Doerr
     */
    public function setFormItemColClass($formItemColClass)
    {
        $this->formItemColClass = $formItemColClass;
        return $this;
    }

    /**
     * @return int
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param int $group
     * @return $this
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return int
     * @author Joachim Doerr
     */
    public function getGroupCount()
    {
        return $this->groupCount;
    }

    /**
     * @param int $groupCount
     * @return $this
     * @author Joachim Doerr
     */
    public function setGroupCount($groupCount)
    {
        $this->groupCount = $groupCount;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getGroupKey()
    {
        return $this->groupKey;
    }

    /**
     * @param string $groupKey
     * @return MFormItem
     * @author Joachim Doerr
     */
    public function setGroupKey($groupKey)
    {
        $this->groupKey = $groupKey;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getInfoTooltip()
    {
        return $this->infoTooltip;
    }

    /**
     * @param string $infoTooltip
     * @return $this
     * @author Joachim Doerr
     */
    public function setInfoTooltip($infoTooltip)
    {
        $this->infoTooltip = $infoTooltip;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getInfoCollapse()
    {
        return $this->infoCollapse;
    }

    /**
     * @param string $infoCollapse
     * @return $this
     * @author Joachim Doerr
     */
    public function setInfoCollapse($infoCollapse)
    {
        $this->infoCollapse = $infoCollapse;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getInfoTooltipIcon()
    {
        return $this->infoTooltipIcon;
    }

    /**
     * @param string $infoTooltipIcon
     * @return $this
     * @author Joachim Doerr
     */
    public function setInfoTooltipIcon($infoTooltipIcon)
    {
        $this->infoTooltipIcon = $infoTooltipIcon;
        return $this;
    }

    /**
     * @return string
     * @author Joachim Doerr
     */
    public function getInfoCollapseIcon()
    {
        return $this->infoCollapseIcon;
    }

    /**
     * @param string $infoCollapseIcon
     * @return $this
     * @author Joachim Doerr
     */
    public function setInfoCollapseIcon($infoCollapseIcon)
    {
        $this->infoCollapseIcon = $infoCollapseIcon;
        return $this;
    }
}
