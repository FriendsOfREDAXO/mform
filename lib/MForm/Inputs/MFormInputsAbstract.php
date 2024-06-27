<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Inputs;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\Utils\MFormModuleHelper;

abstract class MFormInputsAbstract
{
    protected array $config = [];
    protected MForm $mform;

    public function __construct(MForm $mform, array $inputsConfig = [])
    {
        $this->mform = $mform;
        if (!is_null($inputsConfig))
            self::mergeConfig($inputsConfig);
    }

    protected function mergeConfig(array $inputsConfig = []): void
    {
        $this->config = MFormModuleHelper::mergeInputConfig($this->config, $inputsConfig);
    }
}