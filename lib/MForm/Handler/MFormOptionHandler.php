<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;


use MForm\DTO\MFormItem;
use MForm\Utils\MFormClang;
use rex_sql;

class MFormOptionHandler
{
    /**
     * @param MFormItem $item
     * @param $value
     * @param $key
     * @author Joachim Doerr
     */
    public static function addOption(MFormItem $item, $value, $key): void
    {
        $item->options[$key] = MFormClang::getClangValue($value);
    }

    /**
     * set option to item
     * @param MFormItem $item
     * @param $key
     * @author Joachim Doerr
     */
    public static function disableOption(MFormItem $item, $key): void
    {
        $item->disabledOptions[$key] = $key;
    }
    /**
     * @param MFormItem $item
     * @param string $label
     * @param $options
     * @author Joachim Doerr
     */
    public static function addOptGroup(MFormItem $item, string $label, $options): void
    {
        $option = array();
        foreach ($options as $key => $value) {
            // add option to option array
            $option[$key] = MFormClang::getClangValue($value);
        }
        // add option array to options array
        $item->options[$label] = $option;
    }

    /**
     * set option array to item
     * @param MFormItem $item
     * @param array $options
     * @author Joachim Doerr
     */
    public static function setOptions(MFormItem $item, array $options): void
    {
        // if options an array
        foreach ($options as $key => $value) {
            if(is_array($value)) { // and is value an array
                // add opt group by setOptGroup method
                self::addOptGroup($item, $key, $value);
            } else {
                // add default option by setOption method
                self::addOption($item, $value, $key);
            }
        }
    }

    /**
     * @param MFormItem $item
     * @param $options
     * @author Joachim Doerr
     */
    public static function toggleOptions(MFormItem $item, $options): void
    {
        if (is_array($options)) {
            $item->toggleOptions = $options;
        }
    }

    /**
     * @param MFormItem $item
     * @param $keys
     * @author Joachim Doerr
     */
    public static function disableOptions(MFormItem $item, $keys): void
    {
        if (is_array($keys)) {
            $item->disabledOptions = $keys;
        }
    }

    /**
     * set options form sql table as array to item
     * @param MFormItem $item
     * @param $query
     * @author Joachim Doerr
     */
    public static function setSqlOptions(MFormItem $item, $query): void
    {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery($query);
            while ($sql->hasNext()) {
                self::addOption($item, $sql->getValue('name'), $sql->getValue('id'));
                $sql->next();
            }
        } catch (\Exception $e) {
            \rex_logger::logException($e);
        }
    }
}
