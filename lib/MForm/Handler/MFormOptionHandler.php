<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;

use Exception;
use MForm\DTO\MFormItem;
use MForm\Utils\MFormClang;
use rex_logger;
use rex_sql;

use function is_array;

class MFormOptionHandler
{
    /**
     * @author Joachim Doerr
     */
    public static function addOption(MFormItem $item, $value, $key): void
    {
        $item->options[$key] = MFormClang::getClangValue($value);
    }

    /**
     * set option to item.
     * @author Joachim Doerr
     */
    public static function disableOption(MFormItem $item, $key): void
    {
        $item->disabledOptions[$key] = $key;
    }

    /**
     * @author Joachim Doerr
     */
    public static function addOptGroup(MFormItem $item, string $label, $options): void
    {
        $option = [];
        foreach ($options as $key => $value) {
            // add option to option array
            $option[$key] = MFormClang::getClangValue($value);
        }
        // add option array to options array
        $item->options[$label] = $option;
    }

    /**
     * set option array to item.
     * @author Joachim Doerr
     */
    public static function setOptions(MFormItem $item, array $options): void
    {
        // if options an array
        foreach ($options as $key => $value) {
            if (is_array($value)) { // and is value an array
                // add opt group by setOptGroup method
                self::addOptGroup($item, $key, $value);
            } else {
                // add default option by setOption method
                self::addOption($item, $value, $key);
            }
        }
    }

    /**
     * @author Joachim Doerr
     */
    public static function toggleOptions(MFormItem $item, $options): void
    {
        if (is_array($options)) {
            $item->toggleOptions = $options;
        }
    }

    /**
     * @author Joachim Doerr
     */
    public static function disableOptions(MFormItem $item, $keys): void
    {
        if (is_array($keys)) {
            $item->disabledOptions = $keys;
        }
    }

    /**
     * set options form sql table as array to item.
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
        } catch (Exception $e) {
            rex_logger::logException($e);
        }
    }
}
