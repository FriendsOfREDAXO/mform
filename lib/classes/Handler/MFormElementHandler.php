<?php
/**
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

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

        if(sizeof($item->varId) > 3) {
            // TODO exception
        }
        return $item;
    }
}
