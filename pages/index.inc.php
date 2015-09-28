<?php
/**
 * index.inc.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

// add identifier
$name = 'mform';
$path = $REX['INCLUDE_PATH'] . '/addons/' . $name . '/';

// get parameters
$page = rex_request('page', 'string', $name);
$func = rex_request('func', 'string');
$id = rex_request('id', 'int');

// layout top
include_once($REX['INCLUDE_PATH'] . '/layout/top.php');

// title nav
rex_title($I18N->msg($name . '_title') . ' <span class="addonversion" style="font-size:10px;color:silver">' . $REX['ADDON']['version'][$name] . $REX['ADDON'][$name]['rc'] . '</span>', $REX['ADDON']['pages'][$name]);

// include sub pages
require_once($path . '/pages/site.demo.php');
require_once($path . '/pages/site.form.php');
require_once($path . '/pages/site.information.php');

// layout bottom
include_once($REX['INCLUDE_PATH'] . '/layout/bottom.php');

