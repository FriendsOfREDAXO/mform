<?php

/**
 * Einstellungen: Barrierefreiheits-Prüfung für Medien-Felder (#397).
 *
 * @author Friends Of REDAXO
 * @license MIT
 *
 * @var rex_addon $this
 */

// Bestandsinstallationen haben die Werte noch nicht in rex_config: Standard (Prüfung an) vorbelegen,
// sonst würde ein Speichern ohne Änderung die Prüfung abschalten.
if (null === rex_config::get('mform', 'a11y_check')) {
    rex_config::set('mform', 'a11y_check', true);
}
if (null === rex_config::get('mform', 'a11y_default')) {
    rex_config::set('mform', 'a11y_default', false);
}

$form = rex_config_form::factory('mform');

$field = $form->addCheckboxField('a11y_check');
$field->setLabel(rex_i18n::msg('mform_settings_a11y_check'));
$field->addOption(rex_i18n::msg('mform_settings_a11y_check_option'), 1);
$field->setNotice(rex_i18n::msg('mform_settings_a11y_check_notice'));

$field = $form->addCheckboxField('a11y_default');
$field->setLabel(rex_i18n::msg('mform_settings_a11y_default'));
$field->addOption(rex_i18n::msg('mform_settings_a11y_default_option'), 1);
$field->setNotice(rex_i18n::msg('mform_settings_a11y_default_notice'));

$fragment = new rex_fragment();
$fragment->setVar('class', 'edit', false);
$fragment->setVar('title', rex_i18n::msg('mform_settings_a11y_title'), false);
$fragment->setVar('body', $form->get(), false);
echo $fragment->parse('core/page/section.php');
