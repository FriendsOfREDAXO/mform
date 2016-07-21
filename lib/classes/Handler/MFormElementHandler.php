<?php
/**
 * Author: Joachim Doerr
 * Date: 15.07.16
 * Time: 23:15
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
    static public function createElement($id, $type, $varId)
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
