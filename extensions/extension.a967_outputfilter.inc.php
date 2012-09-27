<?php 
/*
extension.a967_outputfilter.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

// INCLUDE CSS INTO BACKEND
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('a967_backend_css'))
{
  function a967_backend_css($params)
  {
    global $strDefaultTemplateThemeName;
    
    $strHeader =
	    PHP_EOL.'<!-- mform -->'.
	    PHP_EOL.'  <link rel="stylesheet" type="text/css" href="include/addons/mform/templates/' . $strDefaultTemplateThemeName . '_theme/theme.css" media="all" />'.
	    PHP_EOL.'<!-- mform -->'.PHP_EOL;
	    	
    return str_replace('</head>',$strHeader.'</head>',$params['subject']);
  }
}
