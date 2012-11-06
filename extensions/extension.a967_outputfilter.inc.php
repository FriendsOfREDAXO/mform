<?php 
/*
extension.a967_outputfilter.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.0
*/

// set css in site head
if (!function_exists('a967_backend_css'))
{
  function a967_backend_css($params)
  {
    $strDefaultTemplateThemeName = rex_addon::get('mform')->getConfig('mform_template');
    
    $strHeader =
	    PHP_EOL.'<!-- mform -->'.
	    PHP_EOL.'  <link rel="stylesheet" type="text/css" href="src/addons/mform/templates/' . $strDefaultTemplateThemeName . '_theme/theme.css" media="all" />'.
	    PHP_EOL.'<!-- mform -->'.PHP_EOL;
	    	
    return str_replace('</head>',$strHeader.'</head>',$params['subject']);
  }
}
