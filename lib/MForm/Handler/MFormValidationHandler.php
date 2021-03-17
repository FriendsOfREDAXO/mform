<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;


use MForm\DTO\MFormItem;

class MFormValidationHandler
{
    const PREFIX = 'data-parsley-';

    /**
     * add default validation
     * @param MFormItem $item
     * @param string $key
     * @param mixed $value
     * @author Joachim Doerr
     */
    public static function addValidation(MFormItem $item, $key, $value = null)
    {
        // set key by value
        switch($value) {
            case 'required':
            case 'empty':
            case 'compare':
            case 'email':
            case 'number':
            case 'float':
            case 'digits':
            case 'alphanum':
            case 'url':
                $key = $value;
                break;
        }

        // add attribute for parsley by key
        switch ($key) {
            case '_removed':
            break;
            default: 
            break;
               
        }
    }

    /**
     * @param MFormItem $item
     * @param $validations
     * @author Joachim Doerr
     */
    public static function setValidations(MFormItem $item, $validations)
    {
        // if validations an array
        if (is_array($validations)) {
            foreach ($validations as $key => $value) {
                // set validation attribute by setValidation method
                self::addValidation($item, $key, $value);
            }
        }
    }
}
