<?php
/*
config.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.4
*/

$strMformAddonPath = $this->getBasePath();
$strDefaultTemplateThemeName = $this->getConfig('mform_template');

$strMode = rex_request('mode', 'string', 'none');

if ($strMode == 'edit')
{
  include $this->getBasePath('extensions/extension.a967_outputfilter.inc.php');
  rex_extension::register('OUTPUT_FILTER', 'a967_backend_css');
}

/*
// ADDON IDENTIFIER
////////////////////////////////////////////////////////////////////////////////
$strAddonName = 'mform';
$strAddonPath = $REX['INCLUDE_PATH'].'/addons/'.$strAddonName;


// ADDON REX COMMONS
////////////////////////////////////////////////////////////////////////////////
$REX['ADDON']['rxid'][$strAddonName] = '967';
$REX['ADDON']['page'][$strAddonName] = $strAddonName;
$REX['ADDON']['name'][$strAddonName] = $strAddonName;
$REX['ADDON'][$strAddonName]['VERSION'] = array('VERSION' => 2, 'MINORVERSION' => 1, 'SUBVERSION' => 2);

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
  
  
  // AUTO INCLUDE FUNCTIONS & BASE CLASSES
  ////////////////////////////////////////////////////////////////////////////////  
  array_walk(glob("$strAddonPath/classes/class.*.inc.php"),create_function('$v,$i', 'return require_once($v);')); 
  array_walk(glob("$strAddonPath/functions/function.*.inc.php"),create_function('$v,$i', 'return require_once($v);')); 
  array_walk(glob("$strAddonPath/extensions/extension.*.inc.php"),create_function('$v,$i', 'return require_once($v);')); 
  
  // GET PARAMS
  ////////////////////////////////////////////////////////////////////////////////
  $strMode = rex_request('mode', 'string', 'none');
  
  // SETTINGS
  ////////////////////////////////////////////////////////////////////////////////  
  $strDefaultTemplateThemeName = $REX["ADDON"]["mform"]["settings"]["default_template_theme_name"];
  
  // EXTENSION POINTS
  ////////////////////////////////////////////////////////////////////////////////
  if ($strMode == 'edit')
  {
    rex_register_extension('OUTPUT_FILTER', 'a967_backend_css');
  }
}
*/