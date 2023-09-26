<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

if (rex_addon::exists('yform') &&
    rex_addon::get('yform')->isAvailable() &&
    rex_plugin::get('yform', 'manager')->isAvailable()) {
    rex_yform::addTemplatePath(rex_path::addon('mform', 'ytemplates'));
    rex_extension::register('MEDIA_IS_IN_USE', 'MformYformHelper::isMediaInUse');
}

if (rex::isBackend()) {
    $mform = rex_addon::get('mform');
    // add toggle files
    rex_view::addCssFile($mform->getAssetsUrl('toggle/toggle.css'));
    rex_view::addJsFile($mform->getAssetsUrl('toggle/toggle.js'));
    // widgets
    rex_view::addCssFile($mform->getAssetsUrl('css/imglist.css'));
    rex_view::addJsFile($mform->getAssetsUrl('js/imglist.js'));
    rex_view::addJsFile($mform->getAssetsUrl('js/customlink.js'));
    // add mform js / css
    rex_view::addJsFile($mform->getAssetsUrl('mform.js'));
    rex_view::addCssFile($mform->getAssetsUrl('css/default_theme.css'));
    // reset count per page init
    if (rex_backend_login::hasSession()) {
        rex_set_session('mform_count', 0);
    }
}
