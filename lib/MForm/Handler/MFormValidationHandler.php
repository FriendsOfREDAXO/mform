<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;


use MForm\DTO\MFormItem;

class MFormValidationHandler
{
    /**
     * add default validation
     * @param MFormItem $item
     * @param string $key
     * @param mixed|null $value
     * @author Joachim Doerr
     */
    public static function setValidation(MFormItem $item, string $key, mixed $value = null): void
    {
        // TODO add only html validations
        // set key by value
//        switch($value) {
//            case 'required':
//            case 'empty':
//            case 'compare':
//            case 'email':
//            case 'number':
//            case 'float':
//            case 'digits':
//            case 'alphanum':
//            case 'url':
//                $key = $value;
//                break;
//        }

        // add attribute for parsley by key
//        switch ($key) {
//            case '_removed':
//            break;
//            default:
//            break;
//        }
    }

    /**
     * @param MFormItem $item
     * @param array $validations
     * @author Joachim Doerr
     */
    public static function setValidations(MFormItem $item, array $validations): void
    {
        // TODO add only html validations
    }
}
