<?php
/*
boot.php

@copyright Copyright (C) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.2.0
*/

if (rex::isBackend())
{
  $files = glob(rex_path::addon('mform')."/functions/function.*.php");
  array_walk($files,create_function('$file', 'return (is_file ( $file )) ? require_once($file) : false;'));
  
  if (rex_request('function', 'string') == 'edit' or rex_request('function', 'string') == 'add')
  {
    rex_view::addCssFile('?mform_theme=' . rex_addon::get('mform')->getConfig('mform_template'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/i18n/messages.de.js'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/parsley.js'));
    
    rex_extension::register('OUTPUT_FILTER', 'a967_add_parsley');
  }
  
  if (rex_request('mform_theme', 'string', '') != '')
  {
   mform_generate_css(rex_request('mform_theme', 'string', rex_addon::get('mform')->getConfig('mform_template')));
   exit;
  }
}
