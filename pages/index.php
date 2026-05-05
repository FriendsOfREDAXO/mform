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

rex_be_controller::includeCurrentPageSubPath();
