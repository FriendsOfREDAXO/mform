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
     * Bedingte Sichtbarkeit an der form-group des Feldes. Akzeptiert eine Bedingung
     * ['field' => 1, 'op' => '=', 'value' => 'a'], eine Liste solcher Arrays bzw. Tripel [1, '=', 'a']
     * oder ['conditions' => [...], 'logic' => 'all'|'any'].
     *
     * @param array<mixed> $value
     */
    private static function applyVisibleIf(MFormItem $item, array $value, string $action = 'show'): void
    {
        $normalized = self::normalizeConditions($value);
        if ([] === $normalized['conditions']) {
            return;
        }

        $formGroupClass = trim((string) ($item->getAttributes()['form-group-class'] ?? ''));
        if (!str_contains(' ' . $formGroupClass . ' ', ' mform-conditional-target ')) {
            $formGroupClass = trim($formGroupClass . ' mform-conditional-target');
        }
        $item->addAttribute('form-group-class', $formGroupClass);

        $formGroupAttributes = $item->getAttributes()['form-group-attributes'] ?? [];
        if (!is_array($formGroupAttributes)) {
            $formGroupAttributes = [];
        }

        $first = $normalized['conditions'][0];
        $formGroupAttributes['data-mform-conditional-source'] = $first['field'];
        $formGroupAttributes['data-mform-conditional-operator'] = $first['op'];
        $formGroupAttributes['data-mform-conditional-value'] = $first['value'];
        $formGroupAttributes['data-mform-conditional-action'] = $action;
        $conditionJson = self::conditionsJson($normalized['conditions'], $action);
        if (null !== $conditionJson) {
            $formGroupAttributes['data-mform-condition'] = $conditionJson;
        }
        if ('any' === $normalized['logic']) {
            $formGroupAttributes['data-mform-condition-logic'] = 'any';
        } else {
            unset($formGroupAttributes['data-mform-condition-logic']);
        }

        $item->addAttribute('form-group-attributes', $formGroupAttributes);
    }

    /**
     * @param array<mixed> $value
     * @return array{conditions: list<array{field: string, op: string, value: string}>, logic: string}
     */
    public static function normalizeConditions(array $value): array
    {
        $logic = 'all';
        if (isset($value['conditions']) && is_array($value['conditions'])) {
            $logic = 'any' === strtolower((string) ($value['logic'] ?? 'all')) ? 'any' : 'all';
            $value = $value['conditions'];
        }
        if (array_key_exists('field', $value)) {
            $value = [$value];
        }

        $conditions = [];
        foreach ($value as $condition) {
            if (!is_array($condition)) {
                continue;
            }
            $field = $condition['field'] ?? $condition[0] ?? null;
            if (!is_scalar($field) || '' === trim((string) $field)) {
                continue;
            }
            $op = trim((string) ($condition['op'] ?? $condition[1] ?? '='));
            $compare = $condition['value'] ?? $condition[2] ?? '';
            $conditions[] = [
                'field' => trim((string) $field),
                'op' => '' === $op ? '=' : $op,
                'value' => is_scalar($compare) ? (string) $compare : '',
            ];
        }

        return ['conditions' => $conditions, 'logic' => $logic];
    }

    /**
     * @param list<array{field: string, op: string, value: string}> $conditions
     */
    public static function conditionsJson(array $conditions, string $action = 'show'): ?string
    {
        $json = json_encode(
            array_map(static fn (array $condition) => $condition + ['action' => $action], $conditions),
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );

        return false === $json ? null : $json;
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
