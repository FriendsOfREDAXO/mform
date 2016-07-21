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
    static public function setValidation(MFormItem $item, $key, $value = null)
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
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'required', 'true');
                break;
            case 'compare':
            case 'email':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'email');
                break;
            case 'number':
            case 'float':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'number');
                break;
            case 'integer':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'integer');
                break;
            case 'digits':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'digits');
                break;
            case 'alphanum':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'alphanum');
                break;
            case 'url':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'url');
                break;
            case 'minlength':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'minlength', $value);
                break;
            case 'maxlength':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'maxlength', $value);
                break;
            case 'Length':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'length', '[' . $value . ']');
                break;
            case 'min':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'min', $value);
                break;
            case 'max':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'max', $value);
                break;
            case 'range':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'range', '[' . $value . ']');
                break;
            case 'Pattern':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'pattern', $value);
                break;
            case 'mincheck':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'mincheck', $value);
                break;
            case 'maxcheck':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'maxcheck', $value);
                break;
            case 'check':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'maxcheck', '[' . $value . ']');
                break;
            case 'equalto':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'equalto', $value);
                break;
            case 'minwords':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'minwords', $value);
                break;
            case 'maxwords':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'maxwords', $value);
                break;
            case 'words':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'words', '[' . $value . ']');
                break;
            case 'dateIso':
                MFormAttributeHandler::setAttribute($item, self::PREFIX . 'type', 'dateIso');
                break;
        }
    }

    /**
     * @param MFormItem $item
     * @param $validations
     * @author Joachim Doerr
     */
    static public function setValidations(MFormItem $item, $validations)
    {
        // if validations an array
        if (is_array($validations)) {
            foreach ($validations as $key => $value) {
                // set validation attribute by setValidation method
                self::setValidation($item, $key, $value);
            }
        }
    }
}
