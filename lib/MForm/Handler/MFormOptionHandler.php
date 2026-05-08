<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Handler;

use Exception;
use FriendsOfRedaxo\MForm\DTO\MFormItem;
use FriendsOfRedaxo\MForm\Utils\MFormClang;
use rex_logger;
use rex_sql;

class MFormOptionHandler
{
    public static function addOption(MFormItem $item, mixed $value, int|string $key): void
    {
        $item->options[$key] = MFormClang::getClangValue($value);
    }

    /**
     * @description set option to item
     */
    public static function disableOption(MFormItem $item, int|string $key): void
    {
        $item->disabledOptions[$key] = $key;
    }
    /**
     * @param array<mixed> $options
     */
    public static function addOptGroup(MFormItem $item, string $label, array $options): void
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
     * @param array<mixed> $options
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
     * @param array<mixed> $options
     */
    public static function toggleOptions(MFormItem $item, array $options): void
    {
        $item->toggleOptions = $options;
    }

    /**
     * @param array<mixed> $keys
     */
    public static function disableOptions(MFormItem $item, array $keys): void
    {
        $item->disabledOptions = $keys;
    }

    /**
     * @description set options form sql table as array to item
     */
    public static function setSqlOptions(MFormItem $item, string $query): void
    {
        try {
            $sql = rex_sql::factory();
            $sql->setQuery($query);
            while ($sql->hasNext()) {
                self::addOption($item, $sql->getValue('name'), (string) $sql->getValue('id'));
                $sql->next();
            }
        } catch (Exception $e) {
            rex_logger::logException($e);
        }
    }
}
