<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

class MFormGroupExtensionHelper
{
    /**
     * @param MFormItem[] $items
     * @return MFormItem[]
     * @author Joachim Doerr
     */
    public static function addTabGroupExtensionItems(array $items)
    {
        return self::addGroupExtensionItems($items, 'tab');
    }

    /**
     * @param MFormItem[] $items
     * @return MFormItem[]
     * @author Joachim Doerr
     */
    public static function addFieldsetGroupExtensionItems(array $items)
    {
        return self::addGroupExtensionItems($items, 'fieldset');
    }

    /**
     * @param MFormItem[] $items
     * @return MFormItem[]
     * @author Joachim Doerr
     */
    public static function addCollapseGroupExtensionItems(array $items)
    {
        return self::addGroupExtensionItems($items, 'collapse');
    }

    /**
     * @param MFormItem[] $items
     * @return MFormItem[]
     * @author Joachim Doerr
     */
    public static function addAccordionGroupExtensionItems(array $items)
    {
        return self::addGroupExtensionItems($items, 'accordion');
    }

    /**
     * @param array $items
     * @param string $type
     * @return array
     * @author Joachim Doerr
     */
    private static function addGroupExtensionItems(array $items, $type)
    {
        $newItems = array();
        $groupCount = 0;
        $count = 1;
        $group = false;
        $toggleAttributes = false;

        /** @var MFormItem $item */
        foreach ($items as $key => $item) {

            $setItem = true;
            $closeGroup = false;

            switch ($item->getType()) {
                case 'checkbox':
                    if (array_key_exists('data-toggle', $item->getAttributes())) {
                        $toggleAttributes = $item->getAttributes();
                        unset($toggleAttributes['data-mform-toggle']);
                    }
                    break;
                case 'select':
                    if (array_key_exists('data-toggle', $item->getAttributes())) {
                        $toggleAttributes = $item->getAttributes();
                    }
                    break;
                case $type:
                    $count++;

                    if (!$group) {
                        $group = true;
                        $count = 1;
                        $groupCount++;
                        // open the new group before the group item will be add to the item list

                        if (is_array($toggleAttributes)) {

                            $mergeArray = array('data-group-select-accordion' => ($toggleAttributes['data-toggle'] == 'accordion') ? 'true' : 'false');

                            if (array_key_exists('hide-toggle-links', $toggleAttributes)) {
                                $mergeArray['data-group-hide-toggle-links'] = ($toggleAttributes['hide-toggle-links']) ? 'true' : 'false';
                            }

                            $item->setAttributes(array_merge($item->getAttributes(), $toggleAttributes, $mergeArray));
                            $toggleAttributes = false;
                        }

                        $newItems[] = self::createGroupItem("start-group-$type", $groupCount, $count, $item);

                    } else {
                        // close prev item
                        $newItems[] = self::createGroupItem("close-$type", $groupCount, ($count - 1));
                    }

                    // add group counts
                    $item->setGroup($groupCount)
                        ->setGroupCount($count);

                    break;
                case 'close-' . $type:
                    if (isset($item->getAttributes()["data-close-group-$type"]) && $item->getAttributes()["data-close-group-$type"] == 1) {
                        // add group counts
                        $item->setGroup($groupCount)
                            ->setGroupCount($groupCount);

                        if (!$group) {
                            // is not group detected break and don't set the item
                            $setItem = false;
                            break;
                        } else {
                            // group is finish
                            $group = false;
                            // group will be closed
                            $closeGroup = true;
                        }
                    } else {
                        $count++;
                        // add group counts
                        $item->setGroup($groupCount)
                            ->setGroupCount($count);
                        $setItem = false;
                    }

                    break;
            }

            // in list is a close item that is not form the same type -> close before you ar in an other list
            if ($group && strpos($item->getType(), "close-") !== false && $item->getType() != "close-$type") {
                $group = false;
                $closeGroup = false;
                $newItems[] = self::createGroupItem("close-$type", $groupCount, $count);
                $newItems[] = self::createGroupItem("close-group-$type", $groupCount, $count);
            }

            // set the item into the new item list
            if ($setItem) {
                $newItems[] = $item;
            }

            // close final group after item close
            if ($closeGroup) {
                $newItems[] = self::createGroupItem("close-group-$type", $groupCount, $count);
            }
        }

        // group and item was not closed do it now
        if ($group) {
            $newItems[] = self::createGroupItem("close-$type", $groupCount, $count);
            $newItems[] = self::createGroupItem("close-group-$type", $groupCount, $count);
        }

        return $newItems;
    }

    /**
     * @param $type
     * @param int $group
     * @param int $groupCount
     * @param MFormItem $item
     * @return MFormItem
     * @author Joachim Doerr
     */
    public static function createGroupItem($type, $group = 0, $groupCount = 0, MFormItem $item = null)
    {
        $newItem = new MFormItem();

        if (!is_null($item) && is_array($item->getAttributes()) && sizeof($item->getAttributes()) > 0) {
            $attributes = array();
            foreach ($item->getAttributes() as $key => $value) {
                if (strpos($key, "data-group-") !== false) {
                    $attributes[$key] = $value;
                }
            }
            $newItem->setAttributes($attributes);
        }

        return $newItem->setType($type)
            ->setGroupCount($groupCount)
            ->setGroup($group);
    }
}