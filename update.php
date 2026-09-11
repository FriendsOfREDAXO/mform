<?php

// old plugin docs still exists ? -> delete
$pluginDocs = __DIR__ . '/plugins/docs';
if (file_exists($pluginDocs)) {
    rex_dir::delete($pluginDocs);
}

$addon = rex_addon::get('mform');
$addon->setProperty('successmsg', '<br><strong>' . rex_i18n::msg('mform_change_message') . '</strong>');

// 10.0: Standardwerte der Barrierefreiheits-Prüfung (Einstellungen) nachziehen.
if (null === rex_config::get('mform', 'a11y_check')) {
    rex_config::set('mform', 'a11y_check', true);
}
if (null === rex_config::get('mform', 'a11y_default')) {
    rex_config::set('mform', 'a11y_default', false);
}
