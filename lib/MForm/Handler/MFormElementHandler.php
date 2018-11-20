<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;


use MForm\DTO\MFormItem;

class MFormElementHandler
{
    /**
     * create basic mform element items
     * @param integer $id
     * @param string $type
     * @param float|integer $varId
     * @return MFormItem
     * @author Joachim Doerr
     */
    public static function createElement($id, $type, $varId)
    {
        // create item
        $item = new MFormItem();
        $item->setId($id) // set id
            ->setVarId(explode('.',$varId)) // set redaxo input value id
            ->setType($type) // set item type
            ->setMode(rex_request('function', 'string')); // set mode add or edit

        return $item;
    }
}
