<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

if (!function_exists('a967_add_parsley')) {
    function a967_add_parsley($params)
    {
        $out = $params->getSubject();
        $init = 'data-validate="parsley" id="REX_FORM">';
    //    $js = $langFile . '<script type="text/javascript" src="' . $REX['HTDOCS_PATH'] . 'files/addons/mform/parsley/parsley.js"></script></head>';
        $out = str_replace('id="REX_FORM">', $init, $out);
    //    $out = str_replace('</head>', $js, $out);
        return $out;
    }
}
