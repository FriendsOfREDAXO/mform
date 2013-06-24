<?php
/*
config.inc.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4.5
@version 2.2.1
*/

// ADDON IDENTIFIER
////////////////////////////////////////////////////////////////////////////////
$strAddonName = 'mform';
$strAddonPath = $REX['INCLUDE_PATH'].'/addons/'.$strAddonName;


// ADDON REX COMMONS
////////////////////////////////////////////////////////////////////////////////
$REX['ADDON']['rxid'][$strAddonName] = '967';
$REX['ADDON']['page'][$strAddonName] = $strAddonName;
$REX['ADDON']['name'][$strAddonName] = $strAddonName;
$REX['ADDON'][$strAddonName]['VERSION'] = array('VERSION' => 2, 'MINORVERSION' => 2, 'SUBVERSION' => 1);
$REX['ADDON'][$strAddonName]['rc'] = '-rc.3';
$REX['ADDON']['version'][$strAddonName] = implode('.', $REX['ADDON'][$strAddonName]['VERSION']);
$REX['ADDON']['author'][$strAddonName] = 'Joachim Doerr';
$REX['ADDON']['supportpage'][$strAddonName] = 'forum.redaxo.de';
$REX['ADDON']['perm'][$strAddonName] = $strAddonName.'[]';  //Allows to add this addon as Startpage
$REX['PERM'][] = $strAddonName.'[]';                        //Allows restriction for users
$REX['EXTRAPERM'][] = $strAddonName.'[extra_perm]';         //Allows Addon specific restrictions (i.e. for Plugins)

// --- DYN
$REX["ADDON"]["mform"]["settings"]["default_template_theme_name"] = 'default';
// --- /DYN

// REDAXO BACKEND
////////////////////////////////////////////////////////////////////////////////
if ($REX['REDAXO'] === true)
{
  // LOAD I18N FILE
  ////////////////////////////////////////////////////////////////////////////////
  $I18N->appendFile(dirname(__FILE__) . '/lang/');
  
  // ADDON MENU
  ////////////////////////////////////////////////////////////////////////////////
  $REX['ADDON']['name'][$strAddonName] = $I18N->msg($strAddonName.'_name');
  $REX['ADDON'][$strAddonName]['SUBPAGES'] = array (
  //        subpage    ,label                         ,perm   ,params               ,attributes
  // array (''         ,'Einstellungen'               ,''     ,''                   ,''),
  // array ('connector','Connector (faceless subpage)',''     ,array('faceless'=>1) ,'' /*array('class'=>'blafasel') can't di: rex_title bug*/),
  );
  
  // AUTO INCLUDE FUNCTIONS & BASE CLASSES
  ////////////////////////////////////////////////////////////////////////////////  
  foreach (array(glob("$strAddonPath/lib/classes/class.*php"),glob("$strAddonPath/lib/functions/function.*php")) as $files)
  {
    array_walk($files,create_function('$file', 'return (is_file ( $file )) ? require_once($file) : false;'));
  }
  
  // GET PARAMS
  ////////////////////////////////////////////////////////////////////////////////
  $strMode = rex_request('function', 'string', 'none');
  
  // SETTINGS
  ////////////////////////////////////////////////////////////////////////////////  
  $strDefaultTemplateThemeName = $REX["ADDON"]["mform"]["settings"]["default_template_theme_name"];
  
  if (rex_request('mform_theme', 'string', '') != '')
  {
    if ($strDefaultTemplateThemeName == '')
    {
      $strDefaultTemplateThemeName = 'default';
    }
    mform_generate_css(rex_request('mform_theme', 'string', $strDefaultTemplateThemeName));
    exit;
  }
  
  // EXTENSION POINTS
  ////////////////////////////////////////////////////////////////////////////////
  if ($strMode == 'edit' or $strMode == 'add')
  {
    rex_register_extension('OUTPUT_FILTER', 'backend_css');
    rex_register_extension('OUTPUT_FILTER', 'add_parsley');
  }
}
