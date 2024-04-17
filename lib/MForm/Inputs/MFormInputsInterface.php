<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Inputs;

use MForm;
use MForm\DTO\MFormInputsConfig;

interface MFormInputsInterface
{
    public function __construct(MForm $mform, MFormInputsConfig $inputsConfig = null);

    public function generateInputs(): MForm;
}