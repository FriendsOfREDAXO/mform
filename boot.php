<?php
/**
 * @author mail[at]doerr-softwaredevelopment[dot]com Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

rex_yform::addTemplatePath(rex_path::addon('mform', 'data/ytemplates'));

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
    // add mform js
    rex_view::addJsFile($this->getAssetsUrl('mform.js'));

    // reset mblock page count
    $_SESSION['mform_count'] = 0;
}
