<?php
/*
function.mform_textileparser.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4.5
@version 2.2.1
*/

// TEXTILE PARSER
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('mfrom_textileparser'))
{
  function mfrom_textileparser($strTextile)
  {
    if(OOAddon::isAvailable("textile"))
    {
      global $REX;
      if($strTextile!='')
      {
        $strTextile = htmlspecialchars_decode($strTextile);
        $strTextile = str_replace("<br />","",$strTextile);
        $strTextile = str_replace("&#039;","'",$strTextile);
        return rex_a79_textile($strTextile);
      }
    }
    else
    {
      $html .= '<pre>'.$strTextile.'</pre>';
      return $html;
    }
  }
}