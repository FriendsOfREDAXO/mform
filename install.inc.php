<?php
/**
 * install.inc.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

// add identifier
$name = rex_request('addonname', 'string');
if (!$name) $name = basename(dirname(__FILE__));

// load lang file
$I18N->appendFile(dirname(__FILE__) . '/lang/');

// install conditions
$requiered_REX = '4.5.0';
$requiered_PHP = 5;
$requiered_addons = array('textile');
$do_install = true;

// rex version check
$this_REX = $REX['VERSION'] . '.' . $REX['SUBVERSION'] . '.' . $REX['MINORVERSION'] = "1";
if (version_compare($this_REX, $requiered_REX, '<')) {
    $REX['ADDON']['installmsg'][$name] = str_replace('###version###', $requiered_REX, $I18N->msg($name . '_install_need_rex'));
    $REX['ADDON']['install'][$name] = 0;
    $do_install = false;
}

// php version check
if (intval(PHP_VERSION) < $requiered_PHP) {
    $REX['ADDON']['installmsg'][$name] = str_replace('###version###', $requiered_REX, $I18N->msg($name . '_install_need_php'));
    $REX['ADDON']['install'][$name] = 0;
    $do_install = false;
}

// required addons check
foreach ($requiered_addons as $a) {
    if (!OOAddon::isInstalled($a)) {
        $REX['ADDON']['installmsg'][$name] = '<br />Addon "' . $a . '" ' . $I18N->msg($name . '_is_not_installed') . '.  >>> <a href="index.php?page=addon&addonname=' . $a . '&install=1">' . $I18N->msg($name . '_install_now') . '</a> <<<';
        $do_install = false;
    } else {
        if (!OOAddon::isAvailable($a)) {
            $REX['ADDON']['installmsg'][$name] = '<br />Addon "' . $a . '" ' . $I18N->msg($name . '_is_not_activated') . '.  >>> <a href="index.php?page=addon&addonname=' . $a . '&activate=1">' . $I18N->msg($name . '_activate_now') . '</a> <<<';
            $do_install = false;
        }
    }
}

// execute install
if ($do_install) {
    $REX['ADDON']['install'][$name] = 1;
}
