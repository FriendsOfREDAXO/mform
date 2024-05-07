<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

namespace FriendsOfRedaxo\MForm\Utils;

use rex;

use function is_array;

class MFormClang
{
    /**
     * @description for multilingual backend
     */
    public static function getClangValue($languageData)
    {
        // lang data must be array
        if (is_array($languageData)) {
            // clean output var
            $langData = '';
            foreach ($languageData as $key => $value) {
                // is user lang in lang data array
                if ($key == rex::getUser()->getLanguage() || $key . '_utf8' == rex::getUser()->getLanguage()) {
                    // set value for exchange
                    $langData = $value;
                }
            }
            // nothing found
            if ('' == $langData) {
                // get first lang array value
                $langData = reset($languageData);
            }
        } else {
            // is lang data not a array return string
            $langData = $languageData;
        }
        return $langData;
    }
}
