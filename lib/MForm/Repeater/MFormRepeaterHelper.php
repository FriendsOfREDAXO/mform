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
                        case 'close-repeater';
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
            }
        }

        return $obj;
    }

    private static function getNameKey(MFormItem $mformItem): string
    {
        return ((is_array($mformItem->getVarId())) ? implode('.', $mformItem->getVarId()) : $mformItem->getVarId());
    }

    private static function addWidgetAttributes(MFormItem $mformItem, string $repeaterId, string $group, string $groups, string|null $parentId): void
    {
        $nameKey = self::getNameKey($mformItem);
        $mformItem->addAttribute('x-model.lazy', $group . '[\'' . $nameKey . '\']')
            ->addAttribute(':id', "'".$nameKey."-'+".$repeaterId."Index".((!empty($parentId))?"+'-'+".$parentId.'Index':''))
            ->addAttribute('group', $group)
            ->addAttribute('groups', $groups)
            ->addAttribute('repeaterId', $repeaterId)
            ->addAttribute('parent_id', $parentId)
            ->addAttribute('item_name_key', $nameKey);
    }
}
