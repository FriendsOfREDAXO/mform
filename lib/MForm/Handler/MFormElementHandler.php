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
     * create basic mform element items.
     * @param int $id
     * @param string $type
     * @param float|int $varId
     * @author Joachim Doerr
     */
    public static function createElement($id, $type, $varId): MFormItem
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
