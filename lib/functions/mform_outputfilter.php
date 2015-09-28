<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.6.x
 * @version 3.0.0
 * @license MIT
 */

if (!function_exists('backend_css')) {
    function backend_css($params)
    {
        global $strDefaultTemplateThemeName;

        $header =
            PHP_EOL . '<!-- mform -->' .
            PHP_EOL . '  <link rel="stylesheet" type="text/css" href="?&mform_theme=' . $strDefaultTemplateThemeName . '" media="all" />' .
            PHP_EOL . '<!-- mform -->' . PHP_EOL;

        return str_replace('</head>', $header . '</head>', $params['subject']);
    }
}
