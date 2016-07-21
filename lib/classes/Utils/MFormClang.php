<?php
/**
 * Author: Joachim Doerr
 * Date: 18.07.16
 * Time: 23:15
 */

class MFormClang
{
    /**
     * for multilingual backend
     * @param array|string $languageData
     * @return mixed|string
     * @author Joachim Doerr
     */
    static public function getClangValue($languageData)
    {
        // lang data must be array
        if (is_array($languageData)) {
            // clean output var
            $langData = '';
            foreach ($languageData as $key => $value) {
                // is user lang in lang data array
                if ($key == rex::getUser()->getLanguage() or $key . '_utf8' == rex::getUser()->getLanguage()) {
                    // set value for exchange
                    $langData = $value;
                }
            }
            // nothing found
            if ($langData == '') {
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
