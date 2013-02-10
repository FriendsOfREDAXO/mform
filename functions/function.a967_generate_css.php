<?php 
/*
function.a967_generate_css.php

@copyright Copyright (C) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.1
*/

if (!function_exists('mform_generate_css'))
{
  function mform_generate_css ($strTemplateTheme)
  {
    while (ob_get_level())
    {
      ob_end_clean(); 
    }
    header("Content-type: text/css");
    if (file_exists(rex_path::addon('mform') . '/templates/' . $strDefaultTemplateThemeName . '_theme/theme.css') === true)
    {
      echo file_get_contents(rex_path::addon('mform') . '/templates/' . $strDefaultTemplateThemeName . '_theme/theme.css', FILE_USE_INCLUDE_PATH);
    }
    else
    {
      echo file_get_contents(rex_path::addon('mform') . '/templates/' . rex_addon::get('mform')->getConfig('mform_template') . '_theme/theme.css', FILE_USE_INCLUDE_PATH);
    }
    die;
  }
}

