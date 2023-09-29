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

use function array_key_exists;
use function count;
use function is_array;

class MFormValueHandler
{
    /**
     * @author Joachim Doerr
     */
    public static function loadRexVars(): array
    {
        $sliceId = rex_request('slice_id', 'int', false);
        $result = [];

        if (false != $sliceId) {
            $table = rex::getTablePrefix() . 'article_slice';
            $fields = '*';
            $where = 'id="' . $sliceId . '"';

            $query = '
                SELECT ' . $fields . '
                FROM ' . $table . '
                WHERE ' . $where;

            try {
                $sql = rex_sql::factory();
                $sql->setQuery($query);
                $rows = $sql->getRows();
                if ($rows > 0) {
                    for ($i = 1; $i <= 20; ++$i) {
                        $result['value'][$i] = $sql->getValue('value' . $i);

                        if ($i <= 10) {
                            $result['filelist'][$i] = $sql->getValue('medialist' . $i);
                            $result['linklist'][$i] = $sql->getValue('linklist' . $i);
                            $result['file'][$i] = $sql->getValue('media' . $i);
                            $result['link'][$i] = $sql->getValue('link' . $i);
                        }

                        // thanks @dtpop
                        $jsonResult = json_decode(htmlspecialchars_decode((string) $result['value'][$i], ENT_NOQUOTES), true); // wb

                        if (is_array($jsonResult)) {
                            $result['value_string'][$i] = $result['value'][$i];
                            $result['value'][$i] = $jsonResult;
                        }
                    }
                }
            } catch (rex_sql_exception $e) {
                rex_logger::logException($e);
            }
        }
        return $result;
    }

    /**
     * @author Joachim Doerr
     */
    public static function decorateItem(MFormItem $item, array $result, ?string $value = null, ?string $defaultValue = null): void
    {
        if (null !== $defaultValue) {
            // set default value
            $item->setDefaultValue(MFormClang::getClangValue($defaultValue));
        }

        $valueString = null;

        if (null === $value && count($result) > 0) {
            // read value by type
            $default = true;
            if (is_array($item->getVarId()) && 1 === count($item->getVarId())) {
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

            if (null !== $valueString) {
                $item->setStringValue($valueString);
            }
            $item->setValue($value);
        } else {
            $item->setValue(MFormClang::getClangValue($value));
        }
    }
}
