<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MFormAttributeHandler
{
    /**
     * set attributes to the item
     * @param MFormItem $item
     * @param mixed $name
     * @param mixed $value
     * @author Joachim Doerr
     */
    public static function addAttribute(MFormItem $item, $name, $value)
    {
        switch ($name) {
            case 'label':
                $item->setLabel($value); // set item label
                break;
            case 'size':
                // is size numeric set number
                if ((is_numeric($value) && $value > 0)) {
                    $item->setSize($value);
                    $item->attributes['size'] = $value;
                }
                // is size full set attribute #sizefull# to replace calculateet size height
                if ($value == 'full') {
                    $item->setSize($value);
                    $item->attributes['size'] = '#sizefull#';
                }
                break;
            case 'full': // set full for markitup or redactor fields to use the default_full template
                $item->setFull(true);
                break;
            case 'multiple': // flag the multiple fields
                $item->setMultiple(true);
                $item->attributes[$name] = $value;
                break;
            case 'category':
            case 'catId': // set cat id as parameter for link or media fields
                if ($value > 0)
                    MFormParameterHandler::addParameter($item, 'category', $value);
                break;
            case 'validation': // add validation by parsley
                if (is_array($value))
                    MFormValidationHandler::setValidations($item, $value);
                break;
            case 'default-value': // set default value for any fields
                $item->setDefaultValue($value);
                break;
            case 'class': // set custom class
                $item->setClass($value);
                break;
            case 'default-class': // i like set the r5 default classes
                $item->setDefaultClass($value);
                break;
            case 'required':
                MFormValidationHandler::addValidation($item, 'required');
                break;
            default: // set any attributes
                $item->attributes[$name] = $value;
        }
    }

    /**
     * set attributes array to item
     * @param MFormItem $item
     * @param array $attributes
     * @author Joachim Doerr
     */
    public static function setAttributes(MFormItem $item, $attributes)
    {
        // if attributes an array
        if (is_array($attributes)) {
            foreach ($attributes as $strName => $strValue) {
                // set attribute by setAttribute method
                self::addAttribute($item, $strName, $strValue);
            }
        }
    }
}
