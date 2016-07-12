<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

if (rex::isBackend())
{
    $files = glob(rex_path::addon('mform') . "/lib/functions/*.php");
    array_walk($files, create_function('$file', 'return (is_file ( $file )) ? require_once($file) : false;'));

    rex_view::addCssFile('?mform_theme=' . rex_addon::get('mform')->getConfig('mform_template'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/i18n/messages.de.js'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/parsley.js'));

    rex_extension::register('OUTPUT_FILTER', 'a967_add_parsley');

    if (rex_request('mform_theme', 'string', '') != '')
    {
        mform_generate_css(rex_request('mform_theme', 'string', rex_addon::get('mform')->getConfig('mform_template')));
        exit;
    }
}
