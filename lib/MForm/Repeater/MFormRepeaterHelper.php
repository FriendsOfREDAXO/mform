<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Repeater;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;

class MFormRepeaterHelper
{
    private const DISABLED_KEY = '__disabled';

    public static function getRepeaterChildKeys(array $items, $key): array
    {
        $next = false;
        $keys = [];

        foreach ($items as $k => $itm) {
            if ($next && $itm instanceof MForm) {
                // prepare obj array
                $keys = array_merge($keys, self::getChildKeys($itm));
                $next = false;
            } else if ($k == $key) {
                $next = true;
            }
        }
        return $keys;
    }

    public static function getChildKeys($mform): array
    {
        $keys = [];

        $items = $mform->getItems();
        foreach ($items as $key => $mformItem) {
            if ($mformItem instanceof MFormItem) {
                $nameKey = self::getNameKey($mformItem);
                if (!empty($nameKey) && $mformItem->getType() === 'repeater') {
                    $keys[$nameKey] = $nameKey;
                    $keys = array_merge($keys, self::getChildKeys($items[$key+1]));
                }
            } else if ($mformItem instanceof MForm && (isset($items[$key-1]) && $items[$key-1] instanceof MFormItem && $items[$key-1]->getType() !== 'repeater')) {
                $keys = array_merge($keys, self::getChildKeys($mformItem));
            }
        }
        return $keys;
    }

    /**
     * @param array<MFormItem|MForm> $items
     */
    public static function prepareChildMForms(array $items, string $key, string $repeaterId, string $group, string $groups, string|null $parentId): array
    {
        $next = false;
        $obj = [];

        foreach ($items as $k => $itm) {
            if ($next && $itm instanceof MForm) {
                // prepare obj array
                $obj = array_merge($obj, self::prepareChildItems($itm, $repeaterId, $group, $groups, $parentId));
                $next = false;
            } else if ($k == $key) {
                $next = true;
            }
        }

        return $obj;
    }

    public static function prepareChildItems($mform, string $repeaterId, string $group, string $groups, string|null $parentId): array
    {
        $obj = [];
        $items = $mform->getItems();
//        dump($items);die;
        foreach ($items as $key => $mformItem) {
            if ($mformItem instanceof MFormItem) {
                $nameKey = self::getNameKey($mformItem);
                // prepare mform items
                if (!empty($nameKey)) {
                    // dump($mformItem->getType());
                    switch ($mformItem->getType()) {
                        case 'repeater':
                            if ($items[$key+1] instanceof MForm) {
                                $mformItem->addAttribute('parent_id', $parentId);
                                // complete the repeater child tree
                                if (isset($mformItem->getAttributes()['open']) && $mformItem->getAttributes()['open'] === true) {
                                    $obj[$nameKey] = [self::prepareChildItems($items[$key+1], $repeaterId, $group, $groups, $parentId)];
                                } else {
                                    $obj[$nameKey] = [];
                                }
                            } else {
                                $obj[$nameKey] = [];
                            }
                        case 'close-repeater':
                            $mformItem->addAttribute('group', 'field')
                                ->addAttribute('groups', 'group.' . $nameKey)
                                ->addAttribute('parent_id', $parentId);
                            break;
                        case 'media':
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            $mformItem->addAttribute('repeater_link', true);
                            $obj[$nameKey] = (!empty($mformItem->getDefaultValue())) ? $mformItem->getDefaultValue() : '';
                            break;
                        case 'link':
                        case 'custom-link':
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            $mformItem->addAttribute('repeater_link', true);
                            // TODO add default value
                            $obj[$nameKey] = ['name' => '', 'id' => ''];
                            break;
                        case 'medialist':
                        case 'linklist':
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            # $mformItem->addAttribute('repeater_link', true);
                        // TODO add default value
                            $obj[$nameKey] = ['list' => []];
                            break;
                        case 'textarea':
                            if (str_contains($mformItem->getClass(), 'cke5-editor')) {
                                $mformItem->addAttribute('repeater_cke', 1);
                                $mformItem->setClass($mformItem->getClass() . ' hidden');
                            }
                        case 'checkbox':
                            foreach ($mformItem->getOptions() as $k => $option) {
                                $mformItem->addAttribute('data-value', (string) $k);
                            }
                        case 'multiselect':
                        case 'select':
                        default:
                            self::addWidgetAttributes($mformItem, $repeaterId, $group, $groups, $parentId);
                            $mformItem->addAttribute('x-on:change', 'updateValues()');
                            $obj[$nameKey] = (!empty($mformItem->getDefaultValue())) ? $mformItem->getDefaultValue() : '';
                            break;
                    }
                }
            } else if (($mformItem instanceof MForm && !isset($items[$key-1])) || ($mformItem instanceof MForm && (isset($items[$key-1]) && $items[$key-1] instanceof MFormItem && $items[$key-1]->getType() !== 'repeater'))) {
                $obj = array_merge($obj, self::prepareChildItems($mformItem, $repeaterId, $group, $groups, $parentId));
                self::addWidgetAttributesByMFormObj($mformItem, $repeaterId, $group, $groups, $parentId);
            } if ($mformItem instanceof MForm) {
                foreach ($mformItem->getItems() as $item) {
                    if ($item instanceof MFormItem && $item->getType() === 'collapse') {
                       self::addWidgetAttributes($item, $repeaterId, $group, $groups, $parentId);
                    }
                }
            }
        }

        return $obj;
    }

    private static function getNameKey(MFormItem $mformItem): string
    {
        return ((is_array($mformItem->getVarId())) ? implode('.', $mformItem->getVarId()) : $mformItem->getVarId());
    }

    private static function addWidgetAttributesByMFormObj(MForm $mform, string $repeaterId, string $group, string $groups, string $parentId): void
    {
        foreach ($mform->getItems() as $item) {
            if ($item instanceof MFormItem && $item->getType() === 'collapse') {
                self::addWidgetAttributes($item, $repeaterId, $group, $groups, $parentId);
            }
        }
    }

    private static function addWidgetAttributes(MFormItem $mformItem, string $repeaterId, string $group, string $groups, string|null $parentId, string|null $nameKey = null): void
    {
        if (empty($nameKey)) $nameKey = self::getNameKey($mformItem);
        $mformItem->addAttribute('x-model', $group . '[\'' . $nameKey . '\']')
            ->addAttribute(':id', "'".$nameKey.'-'.$repeaterId."-'+".$repeaterId."Index".((!empty($parentId) && $parentId != $repeaterId)?"+'-".$parentId."-'+".$parentId.'Index':''))
            ->addAttribute('group', $group)
            ->addAttribute('groups', $groups)
            ->addAttribute('repeaterId', $repeaterId)
            ->addAttribute('parent_id', $parentId)
            ->addAttribute('item_name_key', $nameKey);

        if (count($mformItem->getToggleOptions()) > 0) {
            $toggleOptions = $mformItem->getToggleOptions();
            foreach ($toggleOptions as $key => $toggleOption) {
                if (!is_array($toggleOption)) {
                    $toggleOptions[$key] = [$toggleOption, "'" . $toggleOption . "-'+" . $repeaterId . "Index" . ((!empty($parentId) && $parentId != $repeaterId) ? "+'-'+" . $parentId . 'Index' : '')];
                }
            }
            $mformItem->setToggleOptions($toggleOptions);
        }

//        if($nameKey == '') {
//            dump($mformItem);
//        }

//        dump([$repeaterId, $parentId]);

        if (isset($mformItem->getAttributes()['data-toggle-item'])) {
            $mformItem->addAttribute(':data-toggle-item', "'" . $mformItem->getAttributes()['data-toggle-item'] . "-'+" . $repeaterId . "Index" . ((!empty($parentId) && $parentId != $repeaterId) ? "+'-'+" . $parentId . 'Index' : ''));
        }
        if (isset($mformItem->getAttributes()['data-group-collapse-id'])) {
            $mformItem->addAttribute(':data-group-collapse-id', "'" . $mformItem->getAttributes()['data-group-collapse-id'] . "-'+" . $repeaterId . "Index" . ((!empty($parentId) && $parentId != $repeaterId) ? "+'-'+" . $parentId . 'Index' : ''));
        }
    }

    public static function isItemEnabled(array $item): bool
    {
        if (!array_key_exists(self::DISABLED_KEY, $item)) {
            return true;
        }

        $value = $item[self::DISABLED_KEY];
        if (is_bool($value)) {
            return !$value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value !== 1;
        }

        if (is_string($value)) {
            $normalized = strtolower(trim($value));
            return !in_array($normalized, ['1', 'true', 'yes', 'on'], true);
        }

        return !$value;
    }

    public static function filterEnabledItems(array $items): array
    {
        return array_values(array_filter($items, static function ($item): bool {
            return is_array($item) && self::isItemEnabled($item);
        }));
    }

    /**
     * Decodes a REX_VALUE JSON string and returns only enabled repeater items.
     *
     * Use this in module output code instead of rex_var::toArray() to automatically
     * filter out disabled (offline) items and strip the internal __disabled key.
     *
     * Example:
     *   $rows = MFormRepeaterHelper::decode('REX_VALUE[id=1 output=json]');
     *
     * @param string $rexValue The raw REX_VALUE string (already substituted by REDAXO)
     * @return array<int, array<string, mixed>> Filtered and cleaned items
     */
    public static function decode(string $rexValue): array
    {
        if ('' === $rexValue) {
            return [];
        }

        $decoded = json_decode(html_entity_decode($rexValue, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);

        if (!is_array($decoded)) {
            return [];
        }

        return self::prepareItemsForOutput($decoded);
    }

    public static function prepareItemsForOutput(array $items): array
    {
        $result = [];

        foreach (self::filterEnabledItems($items) as $item) {
            unset($item[self::DISABLED_KEY]);

            foreach ($item as $key => $value) {
                if (is_array($value) && self::isRepeaterItemList($value)) {
                    $item[$key] = self::prepareItemsForOutput($value);
                }
            }

            $result[] = $item;
        }

        return $result;
    }

    private static function isRepeaterItemList(array $value): bool
    {
        if ([] === $value) {
            return true;
        }

        foreach ($value as $item) {
            if (!is_array($item)) {
                return false;
            }
        }

        return true;
    }
}
