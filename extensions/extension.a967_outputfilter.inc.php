<?php 
/*
extension.a967_outputfilter.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 2.1.2
*/

// INCLUDE CSS INTO BACKEND
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('a967_backend_css'))
{
  function a967_backend_css($params)
  {
		$strHeader =
	      PHP_EOL.'<!-- mform -->'.
	    	PHP_EOL.'  <link rel="stylesheet" type="text/css" href="../files/addons/mform/backend.css" media="all" />'.
	    	PHP_EOL.'<!-- mform -->'.PHP_EOL;
	    	
    return str_replace('</head>',$strHeader.'</head>',$params['subject']);
  }
}


if (!function_exists('a967_parse_form_by_outputfilter'))
{
  function a967_parse_form_by_outputfilter($params)
  {
    $strOutput = preg_replace_callback('|<mform>(.*?)</mform>|ism', "a967_mform_p_r_callback", $params['subject']);
    return $strOutput; 
  }
}
if (!function_exists('a967_mform_p_r_callback'))
{
  function a967_mform_p_r_callback($matches)
  {
    return !empty($matches[0]) ? parseMFormTemplate(linesToForm(a967_checkArea($matches[0])),'wrapper') : '';
  }
}
if (!function_exists('a967_checkArea'))
{
  function a967_checkArea($matches)
  {
    return str_replace(
            array(
              chr(13),
              chr(10),
              "<mform:n/>html|",
              "<mform:n/>single_line|",
              "<mform:n/>headline|",  
              "<mform:n/>description|",
              "<mform:n/>hidden|",
              "<mform:n/>text|",
              "<mform:n/>textarea|",
              "<mform:n/>markitup|",
              "<mform:n/>select|",
              "<mform:n/>multiselect|",
              "<mform:n/>radio|",
              "<mform:n/>checkbox|",
              "<mform:n/>link|",
              "<mform:n/>linklist|",
              "<mform:n/>media|",
              "<mform:n/>medialist|"
            ),
            array(
              "",
              "<mform:n/>",
              chr(10)."html|",
              chr(10)."single_line|",
              chr(10)."headline|",  
              chr(10)."description|",
              chr(10)."hidden|",
              chr(10)."text|",
              chr(10)."textarea|",
              chr(10)."markitup|",
              chr(10)."select|",
              chr(10)."multiselect|",
              chr(10)."radio|",
              chr(10)."checkbox|",
              chr(10)."link|",
              chr(10)."linklist|",
              chr(10)."media|",
              chr(10)."medialist|"
            ), trim($matches));
  }
}
