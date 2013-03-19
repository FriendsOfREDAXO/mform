<?php
/*
site.form.php

@copyright Copyright (c) 2013 by Doerr Softwaredevelopment
@author mail[at]joachim-doerr[dot]com Joachim Doerr

@package redaxo4
@version 2.2.0
*/

$strNewThem = rex_request('default_template_theme_name', 'string', false);

if ($strFunc == 'savesettings') {
  $strContent = '';
  foreach ($_GET as $strKey => $strVal) {
    if (!in_array($strKey,array('page','subpage','minorpage','func','submit','PHPSESSID'))) {
      $REX['ADDON'][$mypage]['settings'][$strKey] = $strVal;
      if (is_numeric($strVal))
        $strContent .= '$REX["ADDON"]["'.$strAddonName.'"]["settings"]["'.$strKey.'"] = '.$strVal.';'."\n";
      else
        $strContent .= '$REX["ADDON"]["'.$strAddonName.'"]["settings"]["'.$strKey.'"] = \''.$strVal.'\';'."\n";
    }
  }
  $strFile = $REX['INCLUDE_PATH'].'/addons/'.$strAddonName.'/config.inc.php';
  rex_replace_dynamic_contents($strFile, $strContent);
  
  echo rex_info($I18N->msg($strAddonName.'_settings_saved'));
}

$handle = opendir($strAddonPath.'/templates/');
while ($strDir = readdir($handle)) {
  if ($strDir == '.' or $strDir == '..')
  {
  	continue;
  }
  if (is_dir($strAddonPath.'/templates/'.$strDir))
  {
    $arrDirName = explode('_', $strDir);
    
    $arrThemes[] = array(
      'theme_name'      => $arrDirName[0],
      'theme_path_name' => ucwords(str_replace('_', ' ', $strDir))
    );
  }
}
closedir($handle);

$tmp = new rex_select();
$tmp->setSize(1);
$tmp->setName('default_template_theme_name');
foreach ($arrThemes as $arrTheme)
{
  $tmp->addOption($arrTheme['theme_path_name'],$arrTheme['theme_name']);
}
if ($strNewThem != false)
{
  $tmp->setSelected($strNewThem);
}
else
{
  $tmp->setSelected($REX['ADDON'][$strAddonName]['settings']['default_template_theme_name']);
}
$select = $tmp->get();
 
echo '
<div class="rex-addon-output">
  <div class="rex-form">
  <form action="index.php" method="get" id="settings">
    <input type="hidden" name="page" value="'.$strAddonName.'" />
    <input type="hidden" name="subpage" value="'.$strAddonName.'" />
    <input type="hidden" name="func" value="savesettings" />
        <fieldset class="rex-form-col-1">
          <legend>'.$I18N->msg($strAddonName.'_settings').'</legend>
          <div class="rex-form-wrapper">
            <div class="rex-form-row">
              <p class="rex-form-col-a rex-form-select">
                <label for="select">'.$I18N->msg($strAddonName.'_default_templates').'</label>
                '.$select.'
              </p>
            </div>
            <div class="rex-form-row">
              <p class="rex-form-submit">
                <input class="rex-form-submit" type="submit" id="submit" name="submit" value="'.$I18N->msg($strAddonName.'_save').'" />
              </p>
            </div>
          </div>
        </fieldset>
  </form>
  </div>
</div>
';
