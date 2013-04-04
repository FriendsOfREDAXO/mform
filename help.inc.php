<?php
/*
help.inc.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4.5
@version 2.2.1
*/

// ADDON IDENTIFIER
////////////////////////////////////////////////////////////////////////////////
$strAddonName = 'mform';
$strAddonPath = $REX['INCLUDE_PATH'].'/addons/'.$strAddonName.'/';


// LOAD TEXTILEPARSER
////////////////////////////////////////////////////////////////////////////////
if (!function_exists('mfrom_textileparser'))
{
  require_once( $strAddonPath . '/functions/function.mform_textileparser.php' );
}


// LOAD I18N FILE
////////////////////////////////////////////////////////////////////////////////
if (!OOAddon::isAvailable($strAddonName))
{
  $I18N->appendFile(dirname(__FILE__) . '/lang/');
}


// LOAD DEMO MODUL
////////////////////////////////////////////////////////////////////////////////
require_once( $strAddonPath . '/pages/site.demo.php' );


// HELP CONTENT
////////////////////////////////////////////////////////////////////////////////
?>
<h3 style="clear:both;padding:15px 0;display:block;"><?php echo $I18N->msg($strAddonName.'_help_subheadline_1'); ?></h3>

<?php if (OOAddon::isAvailable($strAddonName)): ?>
<h3 style="clear:both;padding:0 0 15px 0;display:block;">MForm v.<?php echo $REX['ADDON']['version'][$strAddonName].$REX['ADDON'][$strAddonName]['rc']; ?></h3>
<?php endif; ?>

<p style="margin-bottom:15px;"><?php echo $I18N->msg($strAddonName.'_help_infotext_1'); ?></p>
<p style="margin-bottom:15px;"><?php echo mfrom_textileparser($I18N->msg($strAddonName.'_help_infotext_2')); ?></p>

<h3 style="clear:both;padding:15px 0;display:block;"><?php echo $I18N->msg($strAddonName.'_help_subheadline_2'); ?></h3>

<p style="margin-bottom:15px;"><?php echo $I18N->msg($strAddonName.'_help_infotext_3'); ?></p>
<p style="margin-bottom:15px;"><?php echo $I18N->msg($strAddonName.'_help_infotext_4'); ?></p>

<h3 style="clear:both;padding:15px 0;display:block;"><?php echo $I18N->msg($strAddonName.'_help_subheadline_3'); ?></h3>

<style>
.mform-list {
  margin-bottom: 15px;
}
.mform-list ul {
  margin: 0 1.5em;
}
</style>

<div class="mform-list">
  <ul>
    <li>Text-Input- und Hidden-Elemente
      <ul>
        <li><code>addTextField</code></li>
        <li><code>addHiddenField</code></li>
        <li><code>addTextAreaField</code></li>
        <li><code>addTextReadOnlyField</code></li>
        <li><code>addTextAreaReadOnlyField</code></li>
      </ul>
    </li>
    <li>Select-Elemente
      <ul>
        <li><code>addSelectField</code></li>
        <li><code>addMultiSelectField</code></li>
      </ul>
    </li>
    <li>Checkbox- und Radio-Elemente
      <ul>
        <li><code>addCheckboxField</code></li>
        <li><code>addRadioField</code></li>
      </ul>
    </li>
    <li>Strukturelle-Elemente
      <ul>
        <li><code>addHtml</code></li>
        <li><code>addHeadline</code></li>
        <li><code>addDescription</code></li>
        <li><code>addFieldset</code></li>
      </ul>
    </li>
    <li>System-Button-Elemente
      <ul>
        <li><code>addLinkField</code></li>
        <li><code>addLinklistField</code></li>
        <li><code>addMediaField</code></li>
        <li><code>addMedialistField</code></li>
      </ul>
    </li>
    <li>Callback-Element
      <ul>
        <li><code>callback</code></li>
      </ul>
    </li>
  </ul>
</div>
