<?php 
/*
extension.mfrom_outputfilter.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4.5
@version 2.2.1
*/

// INCLUDE CSS INTO BACKEND
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('backend_css'))
{
  function backend_css($params)
  {
    global $strDefaultTemplateThemeName;
    
    $strHeader =
      PHP_EOL.'<!-- mform -->'.
      PHP_EOL.'  <link rel="stylesheet" type="text/css" href="?&mform_theme=' . $strDefaultTemplateThemeName . '" media="all" />'.
      PHP_EOL.'<!-- mform -->'.PHP_EOL;
    
    return str_replace('</head>',$strHeader.'</head>',$params['subject']);
  }
}
