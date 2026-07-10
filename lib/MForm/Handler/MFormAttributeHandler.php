<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Handler;

use FriendsOfRedaxo\MForm\DTO\MFormItem;

class MFormAttributeHandler
{
    /**
     * @param array<string, mixed> $condition
     */
    private static function applyVisibleIf(MFormItem $item, array $condition, string $action = 'show'): void
    {
        $sourceField = isset($condition['field']) ? trim((string) $condition['field']) : '';
        if ('' === $sourceField) {
            return;
        }

        $operator = isset($condition['op']) ? trim((string) $condition['op']) : '=';
        $compareValue = isset($condition['value']) ? (string) $condition['value'] : '';

        $formGroupClass = trim((string) ($item->getAttributes()['form-group-class'] ?? ''));
        $formGroupClass = trim($formGroupClass . ' mform-conditional-target');
        $item->addAttribute('form-group-class', $formGroupClass);

        $formGroupAttributes = $item->getAttributes()['form-group-attributes'] ?? [];
        if (!is_array($formGroupAttributes)) {
            $formGroupAttributes = [];
        }

        $conditionJson = json_encode([
            [
                'field' => $sourceField,
                'op' => $operator,
                'value' => $compareValue,
                'action' => $action,
            ],
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $formGroupAttributes['data-mform-conditional-source'] = $sourceField;
        $formGroupAttributes['data-mform-conditional-operator'] = $operator;
        $formGroupAttributes['data-mform-conditional-value'] = $compareValue;
        $formGroupAttributes['data-mform-conditional-action'] = $action;
        if (false !== $conditionJson) {
            $formGroupAttributes['data-mform-condition'] = $conditionJson;
        }

        $item->addAttribute('form-group-attributes', $formGroupAttributes);
    }

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
                    $size = (int) $value;
                    $item->setSize($size);
                    $item->attributes['size'] = $size;
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
            case 'notice':
                $item->setNotice($value);
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
            case 'btn-class':
            case 'class': // set custom class
                $item->setClass($value);
                break;
            case 'default-class': // i like set the r5 default classes
                $item->setDefaultClass($value);
                break;
            case 'visible_if':
                if (is_array($value)) {
                    self::applyVisibleIf($item, $value);
                }
                break;
            case 'hidden_if':
                if (is_array($value)) {
                    self::applyVisibleIf($item, $value, 'hide');
                }
                break;
            default: // set any attributes
                $item->attributes[$name] = $value;
        }
    }

    /**
     * @description set attributes array to item
        * @param array<string, mixed> $attributes
     */
    public static function setAttributes(MFormItem $item, array $attributes): void
    {
        foreach ($attributes as $strName => $strValue) {
            // set attribute by setAttribute method
            self::addAttribute($item, $strName, $strValue);
        }
    }
}
