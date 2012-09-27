<?php
/*
function.a967_textileparser.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

// TEXTILE PARSER
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('a967_textileparser'))
{
  function a967_textileparser($strTextile)
  {
    if(OOAddon::isAvailable("textile"))
    {
      global $REX;

      if($strTextile!='')
      {
        $strTextile = htmlspecialchars_decode($strTextile);
        $strTextile = str_replace("<br />","",$strTextile);
        $strTextile = str_replace("&#039;","'",$strTextile);
        if (strpos($REX['LANG'],'utf'))
        {
          $html = rex_a79_textile($strTextile);
        }
        else
        {
          $html =  utf8_decode(rex_a79_textile($strTextile));
        }
        return $html;
      }
    }
    else
    {
      $html .= '<pre>'.$strTextile.'</pre>';
      return $html;
    }
  }
}