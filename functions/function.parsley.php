<?php 
/*
function.parsley.inc.php

@author mail[at]ng-websolutions[dot]de Nico Geisler
@update_author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo5
@version 3.2.0
*/

if (!function_exists('a967_add_parsley'))
{
  function a967_add_parsley ($params)
  {
    $out = $params->getSubject();
    
    $init = 'data-validate="parsley" id="REX_FORM">';
    
    $out = str_replace('id="REX_FORM">', $init, $out);
    $out = str_replace('</head>', $js, $out);
    
    return $out;
  }
}