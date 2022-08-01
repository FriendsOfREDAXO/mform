<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace MForm\Handler;


use MForm\DTO\MFormItem;
use MForm\Utils\MFormClang;
use rex;
use rex_logger;
use rex_sql;
use rex_sql_exception;

class MFormValueHandler
{
    /**
     * @return array
     * @author Joachim Doerr
     */
    public static function loadRexVars(): array
    {
        $sliceId = rex_request('slice_id', 'int', false);
        $result = [];

        if ($sliceId != false) {
            $table = rex::getTablePrefix() . 'article_slice';
            $fields = '*';
            $where = 'id="' . $_REQUEST['slice_id'] . '"';

            $query = '
                SELECT ' . $fields . '
                FROM ' . $table . '
                WHERE ' . $where;

            try {
                $sql = rex_sql::factory();
                $sql->setQuery($query);
                $rows = $sql->getRows();
                if ($rows > 0) {
                    for ($i = 1; $i <= 20; $i++) {
                        $result['value'][$i] = $sql->getValue('value' . $i);

                        if ($i <= 10) {
                            $result['filelist'][$i] = $sql->getValue('medialist' . $i);
                            $result['linklist'][$i] = $sql->getValue('linklist' . $i);
                            $result['file'][$i] = $sql->getValue('media' . $i);
                            $result['link'][$i] = $sql->getValue('link' . $i);
                        }

                        // thanks @dtpop
                        $jsonResult = json_decode(htmlspecialchars_decode($result['value'][$i],ENT_NOQUOTES), true); // wb

                        if (is_array($jsonResult)) {
                            $result['value_string'][$i] = $result['value'][$i];
                            $result['value'][$i] = $jsonResult;
                        }
                    }
                }
            } catch (rex_sql_exception $e) {
                rex_logger::logException($e);
            }
        
            if (isset($_POST['save']) && $_POST['save'] == 1) {

                foreach (isset($_POST['REX_INPUT_VALUE']) && $_POST['REX_INPUT_VALUE'] as $key => $value) {
                    $result['value'][$key] = $value;
                }

                foreach (isset($_POST['REX_INPUT_MEDIA']) && $_POST['REX_INPUT_MEDIA'] as $key => $value) {
                    $result['file'][$key] = $value;
                }

                foreach (isset($_POST['REX_INPUT_MEDIALIST']) && $_POST['REX_INPUT_MEDIALIST'] as $key => $value) {
                    $result['filelist'][$key] = $value;
                }

                foreach (isset($_POST['REX_INPUT_LINK']) && $_POST['REX_INPUT_LINK'] as $key => $value) {
                    $result['link'][$key] = $value;
                }
                foreach (isset($_POST['REX_INPUT_LINKLIST']) && $_POST['REX_INPUT_LINKLIST'] as $key => $value) {
                    $result['linklist'][$key] = $value;
                }
            }
        
        
        }
        return $result;
    }

    /**
     * @param MFormItem $item
     * @param array $result
     * @param string|null $value
     * @param string|null $defaultValue
     * @author Joachim Doerr
     */
    public static function decorateItem(MFormItem $item, array $result, string $value = null, string $defaultValue = null): void
    {
        if (!is_null($defaultValue)) {
            // set default value
            $item->setDefaultValue(MFormClang::getClangValue($defaultValue));
        }

        $valueString = null;

        if ($value === NULL && sizeof($result) > 0) {
            // read value by type
            $default = true;
            if (is_array($item->getVarId()) && count($item->getVarId()) === 1) {
                switch ($item->getType()) {
                    case 'linklist':
                        $value = $result['linklist'][$item->getVarId()[0]];
                        $default = false;
                        break;
                    case 'imglist':
                    case 'medialist':
                        $value = $result['filelist'][$item->getVarId()[0]];
                        $default = false;
                        break;
                    case 'link':
                        $value = $result['link'][$item->getVarId()[0]];
                        $default = false;
                        break;
                    case 'media':
                        $value = $result['file'][$item->getVarId()[0]];
                        $default = false;
                        break;
                }
            }

            if ($default) {
                if (array_key_exists('value', $result)) {
                    $value = (array_key_exists($item->getVarId()[0], $result['value'])) ? $result['value'][$item->getVarId()[0]] : '';
                    if (is_array($value) && isset($item->getVarId()[1])) {
                        $value = (array_key_exists($item->getVarId()[1], $value)) ? $value[$item->getVarId()[1]] : '';
                    }
                    if (is_array($value) && isset($item->getVarId()[2])) {
                        $value = (array_key_exists($item->getVarId()[2], $value)) ? $value[$item->getVarId()[2]] : '';
                    }
                }
                if (array_key_exists('value_string', $result) && isset($result['value_string'][$item->getVarId()[0]])) {
                    $valueString = $result['value_string'][$item->getVarId()[0]];
                }
            }

            if (!is_null($valueString)) $item->setStringValue($valueString);
            $item->setValue($value);
        } else {
            $item->setValue(MFormClang::getClangValue($value));
        }
    }
}
