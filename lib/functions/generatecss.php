<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

if (function_exists('mform_generate_css') !== true) {
    function mform_generate_css($template)
    {
        while (ob_get_level()) {
            ob_end_clean();
        }

        header("Content-type: text/css");

//        if (isset($REX['USER']) === true) {
//            if ($REX['USER']->isAdmin() === true) {
//                echo <<<EOT
//          .admin-only,
//          .admin-only *:not(:link):not(:visited) {
//            display:block !important;
//            color:red !important;
//          }
//EOT;
//            } else {
//                echo <<<EOT
//          .admin-only {
//            display:none !important;
//          }
//EOT;
//            }
//        }

        if (file_exists(rex_path::addon('mform') . '/templates/' . $template . '_theme/theme.css') === true) {
            echo file_get_contents(rex_path::addon('mform') . '/templates/' . $template . '_theme/theme.css', FILE_USE_INCLUDE_PATH);
        } else {
            echo file_get_contents(rex_path::addon('mform') . '/templates/' . rex_addon::get('mform')->getConfig('mform_template') . '_theme/theme.css', FILE_USE_INCLUDE_PATH);
        }

        die;
    }
}
