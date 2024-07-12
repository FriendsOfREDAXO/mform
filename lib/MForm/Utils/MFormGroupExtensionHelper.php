<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\DTO\MFormItem;

use function array_key_exists;
use function count;
use function is_array;

class MFormGroupExtensionHelper
{
    /**
     * @param MFormItem[] $items
     */
    public static function addTabGroupExtensionItems(array $items): array
    {
        return self::addGroupExtensionItems($items, 'tab');
    }

    /**
     * @param MFormItem[] $items
     */
    public static function addColumnGroupExtensionItems(array $items): array
    {
        return self::addGroupExtensionItems($items, 'column');
    }

    /**
     * @param MFormItem[] $items
     */
    public static function addCollapseGroupExtensionItems(array $items): array
    {
        return self::addGroupExtensionItems($items, 'collapse');
    }

    /**
     * @param MFormItem[] $items
     */
    public static function addAccordionGroupExtensionItems(array $items): array
    {
        return self::addGroupExtensionItems($items, 'accordion');
    }

    /**
     * @param MFormItem[] $items
     */
    public static function addRepeaterGroupExtensionItems(array $items): array
    {
        return self::addGroupExtensionItems($items, 'accordion');
    }

    /**
     * @param MFormItem[] $items
     * @return MFormItem[]
     */
    private static function addGroupExtensionItems(array $items, string $type): array
    {
        $newItems = array();
        $key = 0;
        $groupKey = 0;
        $groupCount = 0;
        $count = 1;
        $group = false;
        $toggleAttributes = false;

        foreach ($items as $key => $item) {
            if ($item instanceof MFormItem) {
                switch ($item->getType()) {
                    case 'checkbox':
                        if (array_key_exists('data-toggle', $item->getAttributes())) {
                            $toggleAttributes = $item->getAttributes();
                            unset($toggleAttributes['data-mform-toggle']);
                        }
                        // no break
                    case 'select':
                        if (array_key_exists('data-toggle', $item->getAttributes())) {
                            $toggleAttributes = $item->getAttributes();
                        }
                        // no break
                    default:
                        // add default item
                        $newItems[] = $item;
                        break;
                    case $type:
                        // count by typ
                        $count++;

                        if (!$group) {
                            $group = true;
                            $count = 1; // reset count by type for group
                            ++$groupCount; // count by group
                            $groupKey = uniqid($groupCount);

                            // open the new group before the group item will be added to the item list
                            if (is_array($toggleAttributes)) {
                                $mergeArray = ['data-group-select-accordion' => ('accordion' == $toggleAttributes['data-toggle']) ? 'true' : 'false'];
                                if (array_key_exists('hide-toggle-links', $toggleAttributes)) {
                                    $mergeArray['data-group-hide-toggle-links'] = ($toggleAttributes['hide-toggle-links']) ? 'true' : 'false';
                                }
                                $item->setAttributes(array_merge($item->getAttributes(), $toggleAttributes, $mergeArray));
                                $toggleAttributes = false;
                            }
                            // start group for type
                            $newItems[] = self::createGroupItem("start-group-$type", $groupCount, $count, $groupKey, $item);

                        } else {
                            // close prev item by same type in group
                            if ($items[$key - 1] instanceof MFormItem && $items[$key - 1]->type != "close-$type") { // is not closed by other item
                                // close auto
                                $newItems[] = self::createGroupItem("close-$type", $groupCount, ($count - 1), $groupKey, $item);
                            }
                        }

                        // add group counts and id
                        $item->setGroup($groupCount) // group id
                            ->setGroupCount($count) // count of group icons
                            ->setGroupKey($groupKey);

                        // open type
                        $newItems[] = $item;
                        break;
                    case 'close-' . $type:
                        // add group counts and id
                        $item->setGroup($groupCount) // group id
                            ->setGroupCount($count) // count of group icons
                            ->setGroupKey($groupKey);

                        // close type in group
                        $newItems[] = $item;

                        if (isset($items[$key + 1]) && $items[$key + 1] instanceof MFormItem && $items[$key + 1]->type != $type or // next item is not from type close group
                            isset($item->getAttributes()["data-close-group-$type"]) && $item->getAttributes()["data-close-group-$type"] == 1
                        ) {
                            // close group auto
                            $newItems[] = self::createGroupItem("close-group-$type", $groupCount, $count, $groupKey, $item);
                            $group = false;
                        }
                        break;
                }
            } else if ($item instanceof MForm) {
                /** var MForm $item */
                $newItems[] = $item;
                $count++;
            }
        }

        // group and item was not closed do it now
        if ($group) {
            if (isset($newItems[$key]) && $newItems[$key] instanceof MFormItem && $items[$key]->type != "close-$type") {
                $newItems[] = self::createGroupItem("close-$type", $groupCount, $count, $groupKey, $item);
            }
            $newItems[] = self::createGroupItem("close-group-$type", $groupCount, $count, $groupKey, $item);
        }

        return $newItems;
    }

    public static function createGroupItem(string $type, int $group = 0, int $groupCount = 0, string $groupKey = '0', MFormItem $item = null): MFormItem
    {
        $newItem = new MFormItem();

        if (null !== $item && is_array($item->getAttributes()) && count($item->getAttributes()) > 0) {
            $attributes = [];
            foreach ($item->getAttributes() as $key => $value) {
                if (str_contains($key, 'data-group-')) {
                    $attributes[$key] = $value;
                }
            }
            $newItem->setAttributes($attributes);
        }

        return $newItem->setType($type)
            ->setGroupCount($groupCount)
            ->setGroup($group)
            ->setGroupKey($groupKey);
    }
}
