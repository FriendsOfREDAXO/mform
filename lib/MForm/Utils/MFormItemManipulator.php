<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

use FriendsOfRedaxo\MForm\DTO\MFormDefault;
use FriendsOfRedaxo\MForm\DTO\MFormItem;

use function count;
use function is_array;

class MFormItemManipulator
{
    /**
     * @description set value for html out and set subvarId for usage in templates
     */
    public static function setVarAndIds(MFormItem $item): void
    {
        // set value for html out
        $value = $item->getValue();
        if (!is_array($value)) {
            // '0' stays unchanged; null/empty string: no setValue call needed
            if ($value !== '0' && $value !== null && $value !== '') {
                $item->setValue(htmlspecialchars($value));
            }
        } elseif (is_array($item->getVarId()) && 1 === count($item->getVarId())) {
            $item->setValue(htmlspecialchars($item->getStringValue()));
        }

        // is mode add and default value defined
        // getDefaultValue() always returns string (never int, never null)
        $defaultValue = $item->getDefaultValue();
        if ($item->getMode() === 'add' && $defaultValue !== '') {
            $item->setValue(htmlspecialchars($defaultValue));
        }

        // set element id - add var id for unique
        // Guard: varId may already be a string if setVarAndIds was called twice on the same item
        $varId = $item->getVarId();
        if (is_array($varId)) {
            $item->setId($item->getId() . '_' . implode('_', $varId));
            // set varId to exchange
            $item->setVarId('[' . implode('][', $varId) . ']');
        }
    }

    /**
     * @description set custom or default id for unique element id
     */
    public static function setCustomId(MFormItem $item): void
    {
        // set default unique element id
        $item->setId('rv' . $item->getId()); // add alpha prefix for valid html syntax
        foreach ($item->getAttributes() as $key => $value) {
            // check is id in attributes set
            if ('id' == $key) {
                $item->setId($value); // set custom id
            }
        }
    }

    /**
     * @description set default class for r5 default theme
     */
    public static function setDefaultClass(MFormItem $item): void
    {
        // is default class flag set
        if ($item->isDefaultClass()) {
            // set class by mform default dto
            $item->setClass(MFormDefault::$classes[$item->getType()] . ' ' . $item->getClass()); // add default class as first class
        }
    }
}
