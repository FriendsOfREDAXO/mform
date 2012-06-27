<?php
/*
mform function.parseFormTemplate.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

if (!function_exists('parseMFormTemplate')) {
  function parseMFormTemplate($strOutput,$strTemplateKey) {
    
    // open template file
    global $REX;
    $strTemplate = implode(file($REX['INCLUDE_PATH']."/addons/mform/templates/mform_" . $strTemplateKey . ".ini", FILE_USE_INCLUDE_PATH));
    
    preg_match('|<mform:label>(.*?)</mform:label>|ism', $strOutput, $arrLabel);
    preg_match('|<mform:element>(.*?)</mform:element>|ism', $strOutput, $arrElement);
    
    switch ($strTemplateKey) {
          
      case 'default_line':
      
        if ($strTemplate != '') {
          $strOutput = str_replace(array(' />','<mform:label/>','<mform:element/>'), array('/>',$arrLabel[1],$arrElement[1]), $strTemplate);
        }
        
        break;
        
        
        
      case 'single_line':
      case 'wrapper':
      default:
        
        if (isset($arrLabel[1]) === true or isset($arrElement[1]) === true) { $strOutput = $arrLabel[1].$arrElement[1]; }
        if ($strTemplate != '') {
          $strOutput = str_replace(array(' />','<mform:output/>'), array('/>',$strOutput), $strTemplate);
        }
        break;
    }
    return $strOutput;
  }
}