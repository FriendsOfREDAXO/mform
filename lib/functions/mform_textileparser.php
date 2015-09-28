<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.6.x
 * @version 3.0.0
 * @license MIT
 */

if (!function_exists('mfrom_textileparser')) {
    function mfrom_textileparser($string)
    {
        if (OOAddon::isAvailable("textile")) {
            global $REX;
            if ($string != '') {
                $string = htmlspecialchars_decode($string);
                $string = str_replace("<br />", "", $string);
                $string = str_replace("&#039;", "'", $string);
                return rex_a79_textile($string);
            }
        } else {
            $html = '<pre>' . $string . '</pre>';
            return $html;
        }
    }
}
