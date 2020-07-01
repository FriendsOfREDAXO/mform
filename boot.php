<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

use MForm\Utils\MFormThemeHelper;

if (rex_addon::exists('yform') &&
    rex_addon::get('yform')->isAvailable() &&
    rex_plugin::get('yform', 'manager')->isAvailable()) {
    rex_yform::addTemplatePath(rex_path::addon('mform', 'data/ytemplates'));
    rex_extension::register('MEDIA_IS_IN_USE', 'MformYformHelper::isMediaInUse');
}

if (rex::isBackend()) {
    // check theme css is exists
    MFormThemeHelper::themeBootCheck(rex_addon::get('mform')->getConfig('mform_theme'));

    // use theme helper class
    if(MFormThemeHelper::getCssAssets(rex_addon::get('mform')->getConfig('mform_theme'))) {
        // foreach all css files
        foreach (MFormThemeHelper::getCssAssets(rex_addon::get('mform')->getConfig('mform_theme')) as $css) {
            // add assets css file
            rex_view::addCssFile($this->getAssetsUrl($css));
        }
    }

    // add all parsley mform files
    rex_view::addCssFile($this->getAssetsUrl('parsley/parsley.css'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/parsley.min.js'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/extra/validator/dateiso.js'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/extra/validator/words.js'));
    rex_view::addJsFile($this->getAssetsUrl('parsley/i18n/de.js')); // TODO backend lang specific
    // add toggle files
    rex_view::addCssFile($this->getAssetsUrl('toggle/toggle.css'));
    rex_view::addJsFile($this->getAssetsUrl('toggle/toggle.js'));
    // widgets
    rex_view::addCssFile($this->getAssetsUrl('css/imglist.css'));
    rex_view::addJsFile($this->getAssetsUrl('js/imglist.js'));
    rex_view::addJsFile($this->getAssetsUrl('js/customlink.js'));
    // add mform js
    rex_view::addJsFile($this->getAssetsUrl('mform.js'));

    // reset count per page init
    rex_set_session('mform_count', 0);
}
