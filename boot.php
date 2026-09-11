<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

rex_api_function::register('mform_resolve_link', \FriendsOfREDAXO\MForm\Api\ResolveLinkApi::class);
rex_api_function::register('mform_a11y_check', \FriendsOfREDAXO\MForm\Api\A11yCheckApi::class);

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
    // add toggle files
    rex_view::addCssFile($addon->getAssetsUrl('toggle/toggle.css'));
    rex_view::addJsFile($addon->getAssetsUrl('toggle/toggle.js'));
    // gemeinsame Design-Tokens zuerst (alle Widget-Stylesheets mappen darauf)
    rex_view::addCssFile($addon->getAssetsUrl('css/mform-tokens.css'));
    // widgets
    rex_view::addCssFile($addon->getAssetsUrl('css/imglist.css'));
    rex_view::addCssFile($addon->getAssetsUrl('css/list-widget.css'));
    rex_view::addJsFile($addon->getAssetsUrl('js/mediaplace-bridge.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/linkmap-bridge.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/imglist.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/sortable.min.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/list-widget.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/customlink.js'));
    rex_view::addJsFile($addon->getAssetsUrl('js/a11y-check.js'));
    rex_view::setJsProperty('mform_a11y', [
        'api' => rex_url::backendController(['rex-api-call' => 'mform_a11y_check'], false),
        'edit' => rex_i18n::msg('mform_a11y_edit'),
        'recheck' => rex_i18n::msg('mform_a11y_recheck'),
        'blocked' => rex_i18n::msg('mform_a11y_strict_blocked'),
        'checking' => rex_i18n::msg('mform_a11y_checking'),
    ]);
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
