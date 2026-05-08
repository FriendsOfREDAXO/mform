<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\DTO;

class MFormItem
{
    public string $type = "";
    public string $id = "";
    /** @var array<mixed>|string */
    public array|string $varId = "";
    /** @var array<mixed>|string|null */
    public array|string|null $value = "";
    public string $stringValue = "";
    public string $defaultValue = "";
    public string $mode = "";
    public int|string $size = 1;
    /** @var array<string, mixed> */
    public array $attributes = [];
    public string $class = '';
    public bool $defaultClass = true;
    /** @var array<mixed> */
    public array $options = [];
    /** @var array<mixed> */
    public array $disabledOptions = [];
    /** @var array<mixed> */
    public array $toggleOptions = [];
    /** @var array<string, mixed> */
    public array $parameter = [];
    public bool $multiple = false;
    /** @var array<mixed>|string */
    public array|string $label = "";
    public string $legend = "";
    public bool $full = false;
    public string $labelColClass = "";
    public string $formItemColClass = "";
    public int $group = 0;
    public int $groupCount = 0;
    public string $groupKey = "";
    public string $infoTooltip = "";
    public string $infoCollapse = "";
    public string $infoTooltipIcon = "";
    public string $infoCollapseIcon = "";
    public string $notice = "";

    public function getType(): string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;
        return $this;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setId(string $id): static
    {
        $this->id = $id;
        return $this;
    }

    /** @return array<mixed>|string */
    public function getVarId(): array|string
    {
        return $this->varId;
    }

    /** @param array<mixed>|string $varId */
    public function setVarId(array|string $varId): static
    {
        $this->varId = $varId;
        return $this;
    }

    /** @return array<mixed>|string|null */
    public function getValue(): array|string|null
    {
        return $this->value;
    }

    /** @param array<mixed>|string|null $value */
    public function setValue(array|string|null $value): static
    {
        $this->value = $value;
        return $this;
    }

    public function getStringValue(): string
    {
        return $this->stringValue;
    }

    public function setStringValue(string $stringValue): static
    {
        $this->stringValue = $stringValue;
        return $this;
    }

    public function getDefaultValue(): string
    {
        return $this->defaultValue;
    }

    public function setDefaultValue(string $defaultValue): static
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    public function getMode(): string
    {
        return $this->mode;
    }

    public function setMode(string $mode): static
    {
        $this->mode = $mode;
        return $this;
    }

    public function getSize(): int|string
    {
        return $this->size;
    }

    public function setSize(int|string $size): static
    {
        $this->size = $size;
        return $this;
    }

    /** @return array<string, mixed> */
    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function addAttribute(string $key, mixed $value): static
    {
        $this->attributes[$key] = $value;
        return $this;
    }

    /** @param array<string, mixed> $attributes */
    public function setAttributes(array $attributes): static
    {
        $this->attributes = $attributes;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }

    public function isDefaultClass(): bool
    {
        return $this->defaultClass;
    }

    public function setDefaultClass(bool $defaultClass): static
    {
        $this->defaultClass = $defaultClass;
        return $this;
    }

    /** @return array<mixed> */
    public function getOptions(): array
    {
        return $this->options;
    }

    /** @param array<mixed> $options */
    public function setOptions(array $options): static
    {
        $this->options = $options;
        return $this;
    }

    /** @return array<mixed> */
    public function getToggleOptions(): array
    {
        return $this->toggleOptions;
    }

    /** @param array<mixed> $toggleOptions */
    public function setToggleOptions(array $toggleOptions): static
    {
        $this->toggleOptions = $toggleOptions;
        return $this;
    }

    /** @return array<mixed> */
    public function getDisabledOptions(): array
    {
        return $this->disabledOptions;
    }

    /** @param array<mixed> $disabledOptions */
    public function setDisabledOptions(array $disabledOptions): static
    {
        $this->disabledOptions = $disabledOptions;
        return $this;
    }

    /** @return array<string, mixed> */
    public function getParameter(): array
    {
        return $this->parameter;
    }

    /** @param array<string, mixed> $parameter */
    public function setParameter(array $parameter): static
    {
        $this->parameter = $parameter;
        return $this;
    }

    public function isMultiple(): bool
    {
        return $this->multiple;
    }

    public function setMultiple(bool $multiple): static
    {
        $this->multiple = $multiple;
        return $this;
    }

    /** @return array<mixed>|string */
    public function getLabel(): array|string
    {
        return $this->label;
    }

    /** @param array<mixed>|string $label */
    public function setLabel(array|string $label): static
    {
        $this->label = $label;
        return $this;
    }

    public function getLegend(): string
    {
        return $this->legend;
    }

    public function setLegend(string $legend): static
    {
        $this->legend = $legend;
        return $this;
    }

    public function isFull(): bool
    {
        return $this->full;
    }

    public function setFull(bool $full): static
    {
        $this->full = $full;
        return $this;
    }

    public function getLabelColClass(): string
    {
        return $this->labelColClass;
    }

    public function setLabelColClass(string $labelColClass): static
    {
        $this->labelColClass = $labelColClass;
        return $this;
    }

    public function getFormItemColClass(): string
    {
        return $this->formItemColClass;
    }

    public function setFormItemColClass(string $formItemColClass): static
    {
        $this->formItemColClass = $formItemColClass;
        return $this;
    }

    public function getGroup(): int
    {
        return $this->group;
    }

    public function setGroup(int $group): static
    {
        $this->group = $group;
        return $this;
    }

    public function getGroupCount(): int
    {
        return $this->groupCount;
    }

    public function setGroupCount(int $groupCount): static
    {
        $this->groupCount = $groupCount;
        return $this;
    }

    public function getGroupKey(): string
    {
        return $this->groupKey;
    }

    public function setGroupKey(string $groupKey): static
    {
        $this->groupKey = $groupKey;
        return $this;
    }

    public function getInfoTooltip(): string
    {
        return $this->infoTooltip;
    }

    public function setInfoTooltip(string $infoTooltip): static
    {
        $this->infoTooltip = $infoTooltip;
        return $this;
    }

    public function getInfoCollapse(): string
    {
        return $this->infoCollapse;
    }

    public function setInfoCollapse(string $infoCollapse): static
    {
        $this->infoCollapse = $infoCollapse;
        return $this;
    }

    public function getInfoTooltipIcon(): string
    {
        return $this->infoTooltipIcon;
    }

    public function setInfoTooltipIcon(string $infoTooltipIcon): static
    {
        $this->infoTooltipIcon = $infoTooltipIcon;
        return $this;
    }

    public function getInfoCollapseIcon(): string
    {
        return $this->infoCollapseIcon;
    }

    public function setInfoCollapseIcon(string $infoCollapseIcon): static
    {
        $this->infoCollapseIcon = $infoCollapseIcon;
        return $this;
    }

    public function getNotice(): string
    {
        return $this->notice;
    }

    public function setNotice(string $notice): static
    {
        $this->notice = $notice;
        return $this;
    }
}
