<?php
/*
mform config.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 1.2
*/

$mypage = 'mform';

$REX['ADDON']['rxid'][$mypage] = '967';
$REX['ADDON']['page'][$mypage] = $mypage;
$REX['ADDON']['name'][$mypage] = 'MForm';
$REX['ADDON']['perm'][$mypage] = 'mform[]';
$REX['ADDON']['version'][$mypage] = '1.2';
$REX['ADDON']['author'][$mypage] = 'Joachim Doerr';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';


if ($REX['REDAXO'] === true) {
  // check mode
  $strMode = rex_request('mode', 'string');
  $I18N->appendFile($REX['INCLUDE_PATH'].'/addons/'.$mypage.'/lang/');
  
  // if mode edit work
  if ($strMode == 'edit') {
    // include all functions
    require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/functions/function.parseModulInputForm.inc.php';
    require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/functions/function.parseFormTemplate.php';
    require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/functions/function.linesToForm.inc.php';
    require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/functions/function.ceckAndReplaceForm.inc.php';
    require_once $REX['INCLUDE_PATH'].'/addons/' . $mypage . '/functions/function.mform.inc.php';
    
    // generate form output
    rex_register_extension('OUTPUT_FILTER','parseModulInputForm');
    rex_register_extension('OUTPUT_FILTER','mform');
    
  }
}