<?php
/*
help.inc.php

@copyright Copyright (c) 2012 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.1.2
*/

// ADDON IDENTIFIER
////////////////////////////////////////////////////////////////////////////////
$strAddonName = 'mform';
$strAddonPath = $REX['INCLUDE_PATH'].'/addons/'.$strAddonName.'/';


// LOAD TEXTILEPARSER
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('a967_textileparser'))
{
  require_once( $strAddonPath . '/functions/function.a967_textileparser.inc.php' );
}


// LOAD I18N FILE
////////////////////////////////////////////////////////////////////////////////
if (!OOAddon::isAvailable($strAddonName))
{
  $I18N->appendFile(dirname(__FILE__) . '/lang/');
}


// LOAD DEMO MODUL
////////////////////////////////////////////////////////////////////////////////
require_once( $strAddonPath . '/pages/site.demo.inc.php' );


// HELP CONTENT
////////////////////////////////////////////////////////////////////////////////
?>
<h3 style="clear:both;padding:15px 0;display:block;"><?php echo $I18N->msg($strAddonName.'_help_subheadline_1'); ?></h3>
<p style="margin-bottom:15px;"><?php echo $I18N->msg($strAddonName.'_help_infotext_1'); ?></p>
<p style="margin-bottom:15px;"><?php echo a967_textileparser($I18N->msg($strAddonName.'_help_infotext_2')); ?></p>

<h3 style="clear:both;padding:15px 0;display:block;"><?php echo $I18N->msg($strAddonName.'_help_subheadline_2'); ?></h3>
<p style="margin-bottom:15px;"><?php echo $I18N->msg($strAddonName.'_help_infotext_3'); ?></p>
<p style="margin-bottom:15px;"><?php echo $I18N->msg($strAddonName.'_help_infotext_4'); ?></p>


<h2><?php echo $I18N->msg($strAddonName.'_demo_modul'); ?></h2>

<div class="rex-addon-content">
  <div><?php echo rex_highlight_string($strModulInputDemo); ?></div>
</div>
