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
            // Spezialbehandlung für '0' als String oder Zahl
            if ($value === '0' || $value === 0) {
                // '0' bleibt unverändert
            } else {
                $string = htmlspecialchars((!empty($item->getValue())) ? $item->getValue() : '');
                if ($string !== '') {
                    $item->setValue($string);
                }
            }
        } elseif (is_array($item->getVarId()) && 1 == count($item->getVarId())) {
            $item->setValue(htmlspecialchars($item->getStringValue()));
        }

        // is mode add and default value defined
        if ('add' == $item->getMode() && ($item->getDefaultValue() || $item->getDefaultValue() === '0' || $item->getDefaultValue() === 0)) {
            // Spezialbehandlung für Default-Wert '0'
            if ($item->getDefaultValue() === '0' || $item->getDefaultValue() === 0) {
                $item->setValue($item->getDefaultValue());
            } else {
                $string = htmlspecialchars((!empty($item->getDefaultValue())) ? $item->getDefaultValue() : '');
                if ($string !== '') {
                    $item->setValue($string);
                } else {
                    $item->setValue($item->getDefaultValue());
                }
            }
        }

        // set element id - add var id for unique
        $item->setId($item->getId() . '_' . implode('_', $item->getVarId()));

        // set varId to exchagne
        $item->setVarId('[' . implode('][', $item->getVarId()) . ']');
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
