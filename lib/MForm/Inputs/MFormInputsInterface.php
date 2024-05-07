<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Inputs;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormInputsConfig;

interface MFormInputsInterface
{
    public function __construct(MForm $mform, MFormInputsConfig $inputsConfig = null);

    public function generateInputs(): MForm;
}