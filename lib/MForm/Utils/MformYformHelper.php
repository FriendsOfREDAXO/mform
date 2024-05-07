<?php

namespace FriendsOfRedaxo;

use rex_extension_point;
use rex_sql;
use rex_yform_manager_field;
use rex_yform_manager_table;

class MformYformHelper
{
    public static function isMediaInUse(rex_extension_point $ep)
    {
        $params = $ep->getParams();
        $warning = $ep->getSubject();

        $sql = rex_sql::factory();
        $sql->setQuery('SELECT * FROM `' . rex_yform_manager_field::table() . '` LIMIT 0');

        $columns = $sql->getFieldnames();
        $select = in_array('multiple', $columns) ? ', `multiple`' : '';

        $fields = $sql->getArray('SELECT `table_name`, `name`' . $select . ' FROM `' . rex_yform_manager_field::table() . '` WHERE `type_id`="value" AND `type_name` IN("custom_link","imagelist")');
        $fields = \rex_extension::registerPoint(new rex_extension_point('YFORM_MEDIA_IS_IN_USE', $fields));

        if (!count($fields)) {
            return $warning;
        }

        $tables = [];
        $escapedFilename = $sql->escape($params['filename']);
        foreach ($fields as $field) {
            $tableName = $field['table_name'];
            $condition = $sql->escapeIdentifier($field['name']) . ' = ' . $escapedFilename;

            if (isset($field['multiple']) && 1 == $field['multiple']) {
                $condition = 'FIND_IN_SET(' . $escapedFilename . ', ' . $sql->escapeIdentifier($field['name']) . ')';
            }
            $tables[$tableName][] = $condition;
        }

        $messages = '';
        foreach ($tables as $tableName => $conditions) {
            $items = $sql->getArray('SELECT `id` FROM ' . $tableName . ' WHERE ' . implode(' OR ', $conditions));
            if (count($items)) {
                foreach ($items as $item) {
                    $sqlData = \rex_sql::factory();
                    $sqlData->setQuery('SELECT `name` FROM `' . rex_yform_manager_table::table() . '` WHERE `table_name` = "' . $tableName . '"');

                    $messages .= '<li><a href="javascript:openPage(\'index.php?page=yform/manager/data_edit&amp;table_name=' . $tableName . '&amp;data_id=' . $item['id'] . '&amp;func=edit\')">' . $sqlData->getValue('name') . ' [id=' . $item['id'] . ']</a></li>';
                }
            }
        }

        if ('' != $messages) {
            $warning[] = 'Tabelle<br /><ul>' . $messages . '</ul>';
        }

        return $warning;
    }
}
