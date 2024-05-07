<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\DTO;

class MFormInputsConfig
{
    public string|int $id;
    public array $show;
    public array $fieldset;
    public array $label;
    public array $description;
    public array $inputs;
    public array $defaultValue;
    public array $subConfig;

    public function __construct(string|int $id = '', array $show = [], array $fieldset = [], array $label = [], array $description = [], array $inputs = [], array $defaultValue = [], array $subConfig = [])
    {
        $this->id = $id;
        $this->show = $show;
        $this->fieldset = $fieldset;
        $this->label = $label;
        $this->description = $description;
        $this->inputs = $inputs;
        $this->defaultValue = $defaultValue;
        $this->subConfig = $subConfig;
    }

    public function addSubConfig(string $key, MFormInputsConfig $config): self
    {
        $this->subConfig[$key] = $config;
        return $this;
    }
}