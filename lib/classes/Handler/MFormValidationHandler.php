<?php

/**
 * Author: Joachim Doerr
 * Date: 17.07.16
 * Time: 09:47
 */
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
            case 'required':
            case 'empty':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'required', 'true');
                break;
            case 'compare':
            case 'email':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'email');
                break;
            case 'number':
            case 'float':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'number');
                break;
            case 'integer':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'integer');
                break;
            case 'digits':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'digits');
                break;
            case 'alphanum':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'alphanum');
                break;
            case 'url':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'url');
                break;
            case 'minlength':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'minlength', $value);
                break;
            case 'maxlength':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'maxlength', $value);
                break;
            case 'Length':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'length', '[' . $value . ']');
                break;
            case 'min':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'min', $value);
                break;
            case 'max':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'max', $value);
                break;
            case 'range':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'range', '[' . $value . ']');
                break;
            case 'Pattern':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'pattern', $value);
                break;
            case 'mincheck':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'mincheck', $value);
                break;
            case 'maxcheck':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'maxcheck', $value);
                break;
            case 'check':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'maxcheck', '[' . $value . ']');
                break;
            case 'equalto':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'equalto', $value);
                break;
            case 'minwords':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'minwords', $value);
                break;
            case 'maxwords':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'maxwords', $value);
                break;
            case 'words':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'words', '[' . $value . ']');
                break;
            case 'dateIso':
                MFormAttributeHandler::addAttribute($item, self::PREFIX . 'type', 'dateIso');
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
