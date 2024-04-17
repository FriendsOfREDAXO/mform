<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;

use MForm\DTO\MFormItem;

class MFormAttributeHandler
{
    /**
     * @description set attributes to the item
     */
    public static function addAttribute(MFormItem $item, mixed $name, mixed $value): void
    {
        switch ($name) {
            case 'legend':
                $item->setLegend($value);
                break;
            case 'label':
                $item->setLabel($value); // set item label
                break;
            case 'size':
                // is size numeric set number
                if (is_numeric($value) && $value > 0) {
                    $item->setSize($value);
                    $item->attributes['size'] = $value;
                }
                // is size full set attribute #sizefull# to replace calculateet size height
                if ('full' == $value) {
                    $item->setSize($value);
                    $item->attributes['size'] = '#sizefull#';
                }
                break;
            case 'full': // set full for markitup or redactor fields to use the default_full template
                $item->setFull(1 == $value || 'true' == $value || true == $value);
                break;
            case 'item-col-class':
            case 'form-item-col-class':
                $item->setFormItemColClass($value);
                break;
            case 'label-col-class':
                $item->setLabelColClass($value);
                break;
            case 'info-collapse':
                $item->setInfoCollapse($value);
                break;
            case 'info-tooltip':
                $item->setInfoTooltip($value);
                break;
            case 'info-collapse-icon':
                $item->setInfoCollapseIcon($value);
                break;
            case 'info-tooltip-icon':
                $item->setInfoTooltipIcon($value);
                break;
            case 'multiple': // flag the multiple fields
                $item->setMultiple(true);
                $item->attributes[$name] = $value;
                break;
            case 'category':
            case 'catId': // set cat id as parameter for link or media fields
                if ($value > 0) {
                    MFormParameterHandler::addParameter($item, 'category', $value);
                }
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
            default: // set any attributes
                $item->attributes[$name] = $value;
        }
    }

    /**
     * @description set attributes array to item
     */
    public static function setAttributes(MFormItem $item, array $attributes): void
    {
        foreach ($attributes as $strName => $strValue) {
            // set attribute by setAttribute method
            self::addAttribute($item, $strName, $strValue);
        }
    }
}
