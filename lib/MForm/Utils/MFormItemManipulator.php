<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Utils;


use MForm\DTO\MFormDefault;
use MForm\DTO\MFormItem;

class MFormItemManipulator
{
    /**
     * set value for html out
     * set subvar id for useage in templates
     * @param MFormItem $item
     * @author Joachim Doerr
     */
    public static function setVarAndIds(MFormItem $item)
    {
        // set value for html out
        if (!is_array($item->getValue())) {
            $item->setValue(htmlspecialchars(((   !empty($item->getValue()) || $item->getValue() === 0 || $item->getValue() === '0')?$item->getValue():'')));
        } else if (is_array($item->getVarId()) && sizeof($item->getVarId()) == 1) {
            $item->setValue(htmlspecialchars($item->getStringValue()));
        }

        // is mode add and default value defined
        if ($item->getMode() == 'add' && $item->getDefaultValue()) {
            // set default value for value html out
            $item->setValue(htmlspecialchars($item->getDefaultValue()));
        }

        // set element id - add var id for unique
        $item->setId($item->getId() . '_' . implode('_', $item->getVarId()));

        // set varId to exchagne
        $item->setVarId('[' . implode('][', $item->getVarId()) . ']');
    }

    /**
     * set custom or default id for unique element id
     * @param MFormItem $item
     * @author Joachim Doerr
     */
    public static function setCustomId(MFormItem $item)
    {
        // set default unique element id
        $item->setId('rv' . $item->getId()); // add alpha prefix for valid html syntax
        foreach ($item->getAttributes() as $key => $value) {
            // check is id in attributes set
            if ($key == 'id') {
                $item->setId($value); // set custom id
            }
        }
    }

    /**
     * set default class for r5 default theme
     * @param MFormItem $item
     * @author Joachim Doerr
     */
    public static function setDefaultClass(MFormItem $item)
    {
        // is default class flag set
        if ($item->isDefaultClass()) {
            // set class by mform default dto
            $item->setClass(MFormDefault::$classes[$item->getType()] . ' ' . $item->getClass()); // add default class as first class
        }
    }
}
