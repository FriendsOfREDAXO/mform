<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Inputs;

use FriendsOfRedaxo\MForm;

interface MFormInputsInterface
{
    /**
     * @param array<string, mixed> $inputsConfig
     */
    public function __construct(MForm $mform, array $inputsConfig = []);

    public function generateInputsForm(): MForm;
}
