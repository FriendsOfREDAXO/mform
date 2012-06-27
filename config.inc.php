<?php
/*
mform config.inc.php

@author mail[at]joachim-doerr[dot]com Joachim Doerr
@author <a href="http://joachim-doerr.com">joachim-doerr.com</a>

@package redaxo4
@version 2.1.2
*/

// ADDON IDENTIFIER
////////////////////////////////////////////////////////////////////////////////
$mypage = 'mform';
$myroot = $REX['INCLUDE_PATH'].'/addons/'.$mypage.'/';


// ADDON REX COMMONS
////////////////////////////////////////////////////////////////////////////////
$REX['ADDON']['rxid'][$mypage] = '967';
$REX['ADDON']['page'][$mypage] = $mypage;
$REX['ADDON']['name'][$mypage] = $mypage;
$REX['ADDON'][$mypage]['VERSION'] = array('VERSION' => 2, 'MINORVERSION' => 1, 'SUBVERSION' => 2);

$REX['ADDON']['version'][$mypage] = implode('.', $REX['ADDON'][$mypage]['VERSION']);
$REX['ADDON']['author'][$mypage] = 'Joachim Doerr';
$REX['ADDON']['supportpage'][$mypage] = 'forum.redaxo.de';

$REX['ADDON']['perm'][$mypage] = $mypage.'[]';	//Allows to add this addon as Startpage
$REX['PERM'][] = $mypage.'[]';					        //Allows restriction for users
$REX['EXTRAPERM'][] = $mypage.'[extra_perm]';	  //Allows Addon specific restrictions (i.e. for Plugins)


// REDAXO BACKEND
////////////////////////////////////////////////////////////////////////////////
if ($REX['REDAXO'] === true)
{
  // LOAD I18N FILE
  ////////////////////////////////////////////////////////////////////////////////
  $I18N->appendFile(dirname(__FILE__) . '/lang/');
  
  // ADDON MENU
  ////////////////////////////////////////////////////////////////////////////////
  $REX['ADDON']['name'][$mypage] = $I18N->msg('mform_name');  
  $REX['ADDON'][$mypage]['SUBPAGES'] = array (
  //      subpage    ,label                         ,perm   ,params               ,attributes
  # array (''         ,'Einstellungen'               ,''     ,''                   ,''),
  # array ('connector','Connector (faceless subpage)',''     ,array('faceless'=>1) ,'' /*array('class'=>'blafasel') can't di: rex_title bug*/),
  );
  
  // AUTO INCLUDE FUNCTIONS & BASE CLASSES
  ////////////////////////////////////////////////////////////////////////////////
  array_walk(glob($myroot.'classes/class.*.inc.php'),create_function('$v,$i', 'return require_once($v);')); 
  array_walk(glob($myroot.'functions/function.*.inc.php'),create_function('$v,$i', 'return require_once($v);')); 
  array_walk(glob($myroot.'extensions/extension.*.inc.php'),create_function('$v,$i', 'return require_once($v);')); 
  
  // GET PARAMS
  ////////////////////////////////////////////////////////////////////////////////
  $strMode = rex_request('mode', 'string', 'edit');
  
  // EXTENSION POINTS
  ////////////////////////////////////////////////////////////////////////////////
  if ($strMode == 'edit')
  {
    rex_register_extension('OUTPUT_FILTER', 'a967_parse_form_by_outputfilter');
    rex_register_extension('OUTPUT_FILTER', 'a967_backend_css');
  }
}