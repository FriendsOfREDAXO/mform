<?php
/*
boot.php

@copyright Copyright (C) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.1
*/

if (rex::isBackend())
{
  $functions = glob(rex_path::addon('mform')."/functions/function.*.php");
  array_walk($functions,create_function('$v,$i', 'return require_once($v);'));

  if (rex_request('function', 'string') == 'edit' or rex_request('function', 'string') == 'add')
  {
    rex_view::addCssFile('?&mform_theme=' . rex_addon::get('mform')->getConfig('mform_template'));
  }

  if (rex_request('mform_theme', 'string', '') != '')
  {
   mform_generate_css(rex_request('mform_theme', 'string', rex_addon::get('mform')->getConfig('mform_template')));
   exit;
  }
}
