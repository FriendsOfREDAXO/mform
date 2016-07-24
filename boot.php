<?php
/**
 * @copyright Copyright (c) 2015 by Joachim Doerr
 * @author mail[at]joachim-doerr[dot]com Joachim Doerr
 *
 * @package redaxo5
 * @version 4.0.0
 * @license MIT
 */

if (rex::isBackend()) {
    // check theme css is exists
    MFormThemeHelper::themeBootCheck(rex_addon::get('mform')->getConfig('mform_theme'));

    // use theme helper class
    if(sizeof(MFormThemeHelper::getCssAssets(rex_addon::get('mform')->getConfig('mform_theme'))) > 0) {
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
}
