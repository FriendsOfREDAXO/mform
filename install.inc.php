<?php
/*
install.inc.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.2.0
*/

// ADDON IDENTIFIER AUS GET PARAMS
////////////////////////////////////////////////////////////////////////////////
$strAddonName = rex_request('addonname','string');


// LOAD I18N FILE
////////////////////////////////////////////////////////////////////////////////
$I18N->appendFile(dirname(__FILE__) . '/lang/');


// INSTALL CONDITIONS
////////////////////////////////////////////////////////////////////////////////
$requiered_REX = '4.5.0';
$requiered_PHP = 5;
$requiered_addons = array('textile');
$do_install = true;


// CHECK REDAXO VERSION
////////////////////////////////////////////////////////////////////////////////
$this_REX = $REX['VERSION'].'.'.$REX['SUBVERSION'].'.'.$REX['MINORVERSION'] = "1";
if(version_compare($this_REX, $requiered_REX, '<'))
{
	$REX['ADDON']['installmsg'][$strAddonName] = str_replace('###version###', $requiered_REX, $I18N->msg($strAddonName.'_install_need_rex'));
	$REX['ADDON']['install'][$strAddonName] = 0;
	$do_install = false;
}


// CHECK PHP VERSION
////////////////////////////////////////////////////////////////////////////////
if (intval(PHP_VERSION) < $requiered_PHP)
{
	$REX['ADDON']['installmsg'][$strAddonName] = str_replace('###version###', $requiered_REX, $I18N->msg($strAddonName.'_install_need_php'));
	$REX['ADDON']['install'][$strAddonName] = 0;
	$do_install = false;
}


// CHECK REQUIERED ADDONS
////////////////////////////////////////////////////////////////////////////////
foreach($requiered_addons as $a)
{
  if (!OOAddon::isInstalled($a))
  {
    $REX['ADDON']['installmsg'][$strAddonName] = '<br />Addon "'.$a.'" '.$I18N->msg($strAddonName.'_is_not_installed').'.  >>> <a href="index.php?page=addon&addonname='.$a.'&install=1">'.$I18N->msg($strAddonName.'_install_now').'</a> <<<';
    $do_install = false;
  }
  else
  {
    if (!OOAddon::isAvailable($a))
    {
      $REX['ADDON']['installmsg'][$strAddonName] = '<br />Addon "'.$a.'" '.$I18N->msg($strAddonName.'_is_not_activated').'.  >>> <a href="index.php?page=addon&addonname='.$a.'&activate=1">'.$I18N->msg($strAddonName.'_activate_now').'</a> <<<';
      $do_install = false;
    }

  }
}


// DO INSTALL
////////////////////////////////////////////////////////////////////////////////
if ($do_install)
{
	$REX['ADDON']['install'][$strAddonName] = 1;
}
