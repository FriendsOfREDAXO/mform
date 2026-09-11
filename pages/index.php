<?php

/**
 * @author Joachim Doerr
 * @package redaxo5
 * @license MIT
 */

// Einheitliches Titel-Banner für alle Seiten (außer explizit eigene Seiten)
$part2 = rex_be_controller::getCurrentPagePart(2);
if (!in_array($part2, ['info'], true)) {
    echo rex_view::title(rex_i18n::msg('mform_title'));
}

// Vorabversionen (Beta/RC) deutlich kennzeichnen: auf jeder MForm-Seite.
$mformVersion = (string) rex_addon::get('mform')->getVersion();
if (str_contains($mformVersion, '-')) {
    echo rex_view::warning('<strong>' . rex_i18n::msg('mform_prerelease_title', rex_escape($mformVersion)) . '</strong><br>' . rex_i18n::msg('mform_prerelease_text'));
}

rex_be_controller::includeCurrentPageSubPath();
