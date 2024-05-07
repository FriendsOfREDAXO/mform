<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Inputs;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormInputsConfig;
use FriendsOfRedaxo\MForm\Utils\MFormModuleHelper;

abstract class MFormInputsAbstract
{
    protected array $config = [];
    protected MForm $mform;

    public function __construct(MForm $mform, MFormInputsConfig $inputsConfig = null)
    {
        $this->mform = $mform;
        if (!is_null($inputsConfig))
            self::mergeConfig($inputsConfig);
    }

    protected function mergeConfig(MFormInputsConfig $inputsConfig): void
    {
        $config = $this->config;
        foreach (['id', 'show', 'fieldset', 'label', 'description', 'inputs', 'defaultValue', 'subConfig'] as $key) {
            if ($inputsConfig instanceof MFormInputsConfig && property_exists($inputsConfig, $key)) {
                if (is_array($config[$key])) {
                    $config[$key] = MFormModuleHelper::mergeInputConfig($config[$key], $inputsConfig->$key);
                } else {
                    $config[$key] = $inputsConfig->$key;
                }
            }
        }
        $this->config = $config;
    }
}