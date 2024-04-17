<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;

use MForm\DTO\MFormItem;

class MFormElementHandler
{
    /**
     * @description create basic mform element items
     */
    public static function createElement(int $id, string $type, mixed $varId): MFormItem
    {
        // create item
        $item = new MFormItem();
        $item->setId($id) // set id
            ->setVarId(explode('.', $varId)) // set redaxo input value id
            ->setType($type) // set item type
            ->setMode(rex_request('function', 'string')); // set mode add or edit

        return $item;
    }
}
