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
    public static function getClangValue(mixed $languageData): mixed
    {
        // lang data must be array
        if (is_array($languageData)) {
            // clean output var
            $langData = '';
            $userLanguage = rex::getUser()?->getLanguage();
            foreach ($languageData as $key => $value) {
                // is user lang in lang data array
                if (null !== $userLanguage && ($key == $userLanguage || $key . '_utf8' == $userLanguage)) {
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
