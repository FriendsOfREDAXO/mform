<?php
/**
 * site.form.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

// add theme request
$newTheme = rex_request('default_template_theme_name', 'string', false);

// save function
if ($func == 'savesettings') {
    $content = '';
    foreach ($_GET as $key => $value) {
        if (!in_array($key, array('page', 'subpage', 'minorpage', 'func', 'submit', 'PHPSESSID'))) {
            $REX['ADDON'][$name]['settings'][$key] = $value;
            if (is_numeric($value))
                $content .= '$REX["ADDON"]["' . $name . '"]["settings"]["' . $key . '"] = ' . $value . ';' . "\n";
            else
                $content .= '$REX["ADDON"]["' . $name . '"]["settings"]["' . $key . '"] = \'' . $value . '\';' . "\n";
        }
    }
    $file = $REX['INCLUDE_PATH'] . '/addons/' . $name . '/config.inc.php';
    rex_replace_dynamic_contents($file, $content);

    echo rex_info($I18N->msg($name . '_settings_saved'));
}

$handle = opendir($path . '/templates/');

while ($dir = readdir($handle)) {
    if ($dir == '.' or $dir == '..') {
        continue;
    }
    if (is_dir($path . '/templates/' . $dir)) {
        $dirName = explode('_', $dir);

        $themes[] = array(
            'theme_name' => $dirName[0],
            'theme_path_name' => ucwords(str_replace('_', ' ', $dir))
        );
    }
}

closedir($handle);

// create select filed
$tmp = new rex_select();
$tmp->setSize(1);
$tmp->setName('default_template_theme_name');

foreach ($themes as $theme) {
    $tmp->addOption($theme['theme_path_name'], $theme['theme_name']);
}

if ($newTheme != false) {
    $tmp->setSelected($newTheme);
} else {
    $tmp->setSelected($REX['ADDON'][$name]['settings']['default_template_theme_name']);
}

$select = $tmp->get();

// html form
echo '
<div class="rex-addon-output">
  <div class="rex-form">
  <form action="index.php" method="get" id="settings">
    <input type="hidden" name="page" value="' . $name . '" />
    <input type="hidden" name="subpage" value="' . $name . '" />
    <input type="hidden" name="func" value="savesettings" />
    <fieldset class="rex-form-col-1">
      <legend>' . $I18N->msg($name . '_settings') . '</legend>
      <div class="rex-form-wrapper">
        <div class="rex-form-row">
          <p class="rex-form-col-a rex-form-select">
            <label for="select">' . $I18N->msg($name . '_default_templates') . '</label>
            ' . $select . '
          </p>
        </div>
        <div class="rex-form-row">
          <p class="rex-form-submit">
            <input class="rex-form-submit" type="submit" id="submit" name="submit" value="' . $I18N->msg($name . '_save') . '" />
          </p>
        </div>
      </div>
    </fieldset>
  </form>
  </div>
</div>
';
