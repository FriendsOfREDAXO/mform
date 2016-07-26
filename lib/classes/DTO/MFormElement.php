<?php
/**
 * Author: Joachim Doerr
 * Date: 17.07.16
 * Time: 00:06
 */

class MFormElement
{
    const KEY = "<element:%s/>";

    /**
     * @var string
     */
    public $id;

    /**
     * @var string
     */
    public $varId;

    /**
     * @var integer
     * Todo remove
     */
    public $subVarId;

    /**
     * @var string
     */
    public $value;

    /**
     * @var string
     */
    public $label;

    /**
     * @var string
     */
    public $attributes;

    /**
     * @var string
     */
    public $options;

    /**
     * @var string
     */
    public $parameter;

    /**
     * @var string
     */
    public $type;

    /**
     * @var string
     */
    public $hidden;

    /**
     * @var string
     */
    public $javascript;

    /**
     * @var string
     */
    public $class;

    /**
     * @var string
     */
    public $legend;

    /**
     * @var string
     */
    public $element;

    /**
     * @var string
     */
    public $output;

    /**
     * @var string
     */
    public $datalist;

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
     * @param string $varId
     * @return $this
     */
    public function setVarId($varId)
    {
        $this->varId = $varId;
        return $this;
    }

    /**
     * @param int $subVarId
     * @return $this
     * TODO remove
     */
    public function setSubVarId($subVarId)
    {
        $this->subVarId = $subVarId;
        return $this;
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
     * @param string $label
     * @return $this
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $attributes
     * @return $this
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
        return $this;
    }

    /**
     * @param string $options
     * @return $this
     */
    public function setOptions($options)
    {
        $this->options = $options;
        return $this;
    }

    /**
     * @param string $parameter
     * @return $this
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
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
     * @param string $hidden
     * @return $this
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
        return $this;
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
     * @param string $javascript
     * @return $this
     */
    public function setJavascript($javascript)
    {
        $this->javascript = $javascript;
        return $this;
    }

    /**
     * @param string $legend
     * @return $this
     */
    public function setLegend($legend)
    {
        $this->legend = $legend;
        return $this;
    }

    /**
     * @param string $element
     * @return $this
     */
    public function setElement($element)
    {
        $this->element = $element;
        return $this;
    }

    /**
     * @param string $output
     * @return $this
     */
    public function setOutput($output)
    {
        $this->output = $output;
        return $this;
    }

    /**
     * @param string $datalist
     * @return $this
     */
    public function setDatalist($datalist)
    {
        $this->datalist = $datalist;
        return $this;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getKeys()
    {
        $keys = array();
        foreach (get_object_vars($this) as $f => $v) {
            $keys[] = sprintf(self::KEY, $f);
        }
        return $keys;
    }

    /**
     * @return array
     * @author Joachim Doerr
     */
    public function getValues()
    {
        $values = array();
        foreach (get_object_vars($this) as $f => $v) {
            $values[] = $v;
        }
        return $values;
    }
}
