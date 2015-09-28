<?php
/**
 * config.inc.php
 * @copyright Copyright (c) 2015 by Doerr Softwaredevelopment
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo4.5
 * @version 3.0.0
 */

// add identifier
$name = 'mform';
$path = $REX['INCLUDE_PATH'] . '/addons/' . $name;

// addon rex commons
$REX['ADDON']['rxid'][$name] = '967';
$REX['ADDON']['page'][$name] = $name;
$REX['ADDON']['name'][$name] = $name;
$REX['ADDON'][$name]['VERSION'] = array('VERSION' => 3, 'MINORVERSION' => 0, 'SUBVERSION' => 0);
$REX['ADDON'][$name]['rc'] = '-rc.1';
$REX['ADDON']['version'][$name] = implode('.', $REX['ADDON'][$name]['VERSION']);
$REX['ADDON']['author'][$name] = 'Joachim Doerr';
$REX['ADDON']['supportpage'][$name] = 'forum.redaxo.de';
$REX['ADDON']['perm'][$name] = $name . '[]';  //Allows to add this addon as Startpage
$REX['PERM'][] = $name . '[]';                        //Allows restriction for users
$REX['EXTRAPERM'][] = $name . '[extra_perm]';         //Allows Addon specific restrictions (i.e. for Plugins)

// --- DYN
$REX["ADDON"]["mform"]["settings"]["default_template_theme_name"] = 'default';
// --- /DYN

// rex backend
if ($REX['REDAXO'] === true) {
    // load lang file
    $I18N->appendFile(dirname(__FILE__) . '/lang/');

    // addon menu
    $REX['ADDON']['name'][$name] = $I18N->msg($name . '_name');
    $REX['ADDON'][$name]['SUBPAGES'] = array(
        //        subpage    ,label                         ,perm   ,params               ,attributes
        // array (''         ,'Einstellungen'               ,''     ,''                   ,''),
        // array ('connector','Connector (faceless subpage)',''     ,array('faceless'=>1) ,'' /*array('class'=>'blafasel') can't di: rex_title bug*/),
    );

    // auto include functions and classes
    foreach (array(glob("$path/lib/classes/*.php"), glob("$path/lib/functions/*.php")) as $files) {
        array_walk($files, create_function('$file', 'return (is_file ( $file )) ? require_once($file) : false;'));
    }

    // get parameters
    $mode = rex_request('function', 'string', 'none');

    // settings
    $defaultTemplate = $REX["ADDON"]["mform"]["settings"]["default_template_theme_name"];

    if (rex_request('mform_theme', 'string', '') != '') {
        if ($defaultTemplate == '') {
            $defaultTemplate = 'default';
        }
        mform_generate_css(rex_request('mform_theme', 'string', $defaultTemplate));
        exit;
    }

    // use extension points
    if ($mode == 'edit' or $mode == 'add') {
        rex_register_extension('OUTPUT_FILTER', 'backend_css');
        rex_register_extension('OUTPUT_FILTER', 'add_parsley');
    }
}
