<?php 
/*
mform function.linesToForm.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

if (!function_exists('linesToForm')) {
  function linesToForm ($strMatches) {
    $arrRows = preg_split('/[\r\n]+/', $strMatches, -1, PREG_SPLIT_NO_EMPTY);
    foreach ($arrRows as $inputLine) {
      $inputLine = str_replace("\t","",$inputLine);
      $arrLine = explode('|' ,$inputLine);
      $strOutput .= ceckAndReplaceForm($arrLine);
    }
    return $strOutput;
  }
}