<?php
/*
function.parsley.php

@package redaxo4
@version 2.2.0
*/

if (!function_exists('add_parsley'))
{
  function add_parsley($params) {
    global $REX;
    $out = $params['subject'];
    
    if($REX['VERSION'].$REX['SUBVERSION'] <= "44")
    {
      $jquery = '<script src="'.$REX['HTDOCS_PATH'].'files/addons/mform/jquery-1.8.3.min.js"';
      $out = str_replace('<script src="media/jquery.min.js"', $jquery, $out);
    }
    
    switch ($REX['LOGIN']->getLanguage())
    {
      case 'de_de_utf8':
      case 'de_de':
        $langFile = '<script type="text/javascript" src="'.$REX['HTDOCS_PATH'].'files/addons/mform/parsley/i18n/messages.de.js"></script>';
        break;
      
      case 'en_gb_utf8':
      case 'en_gb':
      default:
        $langFile = '';
        break;
    }
    
    $init = 'data-validate="parsley" id="REX_FORM">';
    $js = $langFile.'<script type="text/javascript" src="'.$REX['HTDOCS_PATH'].'files/addons/mform/parsley/parsley.js"></script></head>';
    
    $out = str_replace('id="REX_FORM">', $init, $out);
    $out = str_replace('</head>', $js, $out);
    
    return $out;
  }
}
