<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\DTO;

class MFormElement
{
    public string $id = "";
    public string $varId = "";
    public string $value = "";
    public string $label = "";
    public string $attributes = "";
    public string $options = "";
    public string $parameter = "";
    public string $type = "";
    public string $hidden = "";
    public string $javascript = "";
    public string $class = "";
    public string $legend = "";
    public string $element = "";
    public string $output = "";
    public string $datalist = "";
    public string $labelColClass = "";
    public string $formItemColClass = "";
    public string $infoTooltipIcon = "";
    public string $infoTooltip = "";
    public string $infoCollapseButton = "";
    public string $infoCollapse = "";

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    public function setVarId(string $varId): static
    {
        $this->varId = $varId;
        return $this;
    }

    public function setValue(string $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function setLabel(string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function setAttributes(string $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function setOptions(string $options): static
    {
        $this->options = $options;
        return $this;
    }

    public function setParameter(string $parameter): static
    {
        $this->parameter = $parameter;
        return $this;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function setHidden(string $hidden): static
    {
        $this->hidden = $hidden;
        return $this;
    }

    public function setJavascript(string $javascript): static
    {
        $this->javascript = $javascript;
        return $this;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function setLegend(string $legend): static
    {
        $this->legend = $legend;
        return $this;
    }

    public function setElement(string $element): static
    {
        $this->element = $element;
        return $this;
    }

    public function setOutput(string $output): static
    {
        $this->output = $output;
        return $this;
    }

    public function setDatalist(string $datalist): static
    {
        $this->datalist = $datalist;
        return $this;
    }

    public function setLabelColClass(string $labelColClass): static
    {
        $this->labelColClass = $labelColClass;
        return $this;
    }

    public function setFormItemColClass(string $formItemColClass): static
    {
        $this->formItemColClass = $formItemColClass;
        return $this;
    }

    public function setInfoTooltipIcon(string $infoTooltipIcon): static
    {
        $this->infoTooltipIcon = $infoTooltipIcon;
        return $this;
    }

    public function setInfoTooltip(string $infoTooltip): static
    {
        $this->infoTooltip = $infoTooltip;
        return $this;
    }

    public function setInfoCollapseButton(string $infoCollapseButton): static
    {
        $this->infoCollapseButton = $infoCollapseButton;
        return $this;
    }

    public function setInfoCollapse(string $infoCollapse): static
    {
        $this->infoCollapse = $infoCollapse;
        return $this;
    }

    public function getKeys(): array
    {
        $keys = [];
        foreach (get_object_vars($this) as $f => $v) {
            $keys[] = $f;
        }
        return $keys;
    }

    public function getValues(): array
    {
        $values = [];
        foreach (get_object_vars($this) as $f => $v) {
            $values[] = $v;
        }
        return $values;
    }
}
