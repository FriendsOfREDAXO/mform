<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

rex_api_function::register('mform_resolve_link', \FriendsOfREDAXO\MForm\Api\ResolveLinkApi::class);

$addon = rex_addon::get('mform');

if (rex_addon::exists('yform') && rex_addon::get('yform')->isAvailable()) {
    rex_yform::addTemplatePath(rex_path::addon('mform', 'ytemplates'));

    $yform = rex_addon::get('yform');
    if (version_compare($yform->getVersion(), '5.0.0-beta1', '<')) {
        if (rex_plugin::get('yform', 'manager')->isAvailable()) {
            rex_extension::register('MEDIA_IS_IN_USE', "FriendsOfRedaxo\\MformYformHelper::isMediaInUse");
        }
    } else {
        rex_extension::register('MEDIA_IS_IN_USE', "FriendsOfRedaxo\\MformYformHelper::isMediaInUse");
    }
}

if (rex::isBackend()) {
    rex_extension::register('PAGES_PREPARED', static function (): void {
        if (rex::isDebugMode()) {
            return;
        }

        $demoPage = rex_be_controller::getPageObject('mform/demo');
        if (!$demoPage) {
            return;
        }

        $subpages = $demoPage->getSubpages();
        if (!isset($subpages['demo_renderer_parity'])) {
            return;
        }

        unset($subpages['demo_renderer_parity']);
        $demoPage->setSubpages($subpages);
    });

    // add toggle files
    rex_view::addCssFile($addon->getAssetsUrl('toggle/toggle.css'));
    rex_view::addJsFile($addon->getAssetsUrl('toggle/toggle.js'));
    // widgets
    rex_view::addCssFile($addon->getAssetsUrl('css/imglist.css'));
    rex_view::addCssFile($addon->getAssetsUrl('css/list-widget.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/imglist.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/sortable.min.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/list-widget.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/customlink.js'));
    // add mform js / css
    rex_view::addJsFile($addon->getAssetsUrl('mform.js'));
    rex_view::addCssFile($addon->getAssetsUrl('css/mform.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/flex-repeater.js'));
    rex_view::addCssFile($addon->getAssetsUrl('css/flex-repeater.css'));
    // form builder (only on its own page)
    if (str_starts_with((string) rex_be_controller::getCurrentPage(), 'mform/formbuilder')) {
        rex_view::addCssFile($addon->getAssetsUrl('css/formbuilder.css'));
        rex_view::addJsFile($addon->getAssetsUrl('js/formbuilder.js'));
    }
    // docs + demo pages
    if (
        str_starts_with((string) rex_be_controller::getCurrentPage(), 'mform/docs')
        || str_starts_with((string) rex_be_controller::getCurrentPage(), 'mform/demo')
    ) {
        rex_view::addCssFile($addon->getAssetsUrl('css/docs.css'));
        rex_view::addJsFile($addon->getAssetsUrl('js/docs.js'));
    }
    // reset count per page init
    if (rex_backend_login::hasSession()) {
        rex_set_session('mform_count', 0);
    }
}
