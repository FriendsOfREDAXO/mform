<?php
/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

echo rex_view::title(rex_i18n::msg('mform_title') . ' ' . rex_i18n::msg('mform_'.rex_be_controller::getCurrentPagePart(2)));

$headline = '<h3>' . rex_i18n::msg('mform_help_subheadline_1') . '</h3>';
$content = '<p>' . rex_i18n::msg('mform_help_infotext_1') . '</p>
    <p>' . rex_i18n::msg('mform_help_infotext_2') . '</p>
    <p>' . rex_i18n::msg('mform_help_infotext_3') . '</p>
    <a href="https://github.com/FriendsOfREDAXO/mform/" target="_blank">' . rex_i18n::msg('mform_github') . '</a>';