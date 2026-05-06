<?php

/**
 * Full Feature Lab – Output
 *
 * Zeigt alle gespeicherten Werte des Moduls als Debug-Ausgabe.
 *
 * REX_VALUE[1..11] – Einzelfelder (Grunddaten, Darstellung, Widgets, Erweiterte Einstellungen)
 * REX_VALUE[12]    – Repeater „Inhaltszeilen" (JSON-Array, offline-Items gefiltert)
 * REX_VALUE[13]    – Custom Link Multiple (JSON-Array von Link-Strings)
 */

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// ── Einzelfelder ─────────────────────────────────────────────────────────────
$headline   = 'REX_VALUE[1]';
$language   = 'REX_VALUE[2]';
$feature    = 'REX_VALUE[3]';
$layout     = 'REX_VALUE[4]';
$bgColor    = 'REX_VALUE[5]';
$mainLink   = 'REX_VALUE[6]';
$imglist    = 'REX_VALUE[7]';
$linklist   = 'REX_VALUE[8]';
$medialist  = 'REX_VALUE[9]';
$hint       = 'REX_VALUE[10]';
$alignment  = 'REX_VALUE[11]';
$multiLinks = 'REX_VALUE[13]';

// ── Repeater-Zeilen – Rohdaten fuer Statusanzeige + gefilterte Ausgabe ─────────
$rawRows = json_decode(html_entity_decode('REX_VALUE[12]', ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
$rows = is_array($rawRows) ? $rawRows : [];

// ── Multiple Links (JSON) ─────────────────────────────────────────────────────
$multiLinksArr = [];
if ($multiLinks !== '') {
    $decoded = json_decode(html_entity_decode($multiLinks, ENT_QUOTES | ENT_HTML5, 'UTF-8'), true);
    if (is_array($decoded)) {
        $multiLinksArr = $decoded;
    }
}

// ── Ausgabe ───────────────────────────────────────────────────────────────────
echo '<div class="panel panel-default" style="margin:1rem">';
echo '<div class="panel-heading"><strong>Full Feature Lab – Debug-Ausgabe</strong></div>';
echo '<div class="panel-body">';

echo '<h4>Grunddaten</h4>';
echo '<table class="table table-condensed table-bordered">';
echo '<tr><th>Headline</th><td>' . htmlspecialchars($headline) . '</td></tr>';
echo '<tr><th>Sprache</th><td>' . htmlspecialchars($language) . '</td></tr>';
echo '<tr><th>Feature aktiv</th><td>' . ($feature ? 'Ja' : 'Nein') . '</td></tr>';
echo '</table>';

echo '<h4>Darstellung</h4>';
echo '<table class="table table-condensed table-bordered">';
echo '<tr><th>Layout</th><td>' . htmlspecialchars($layout) . '</td></tr>';
echo '<tr><th>Hintergrundfarbe</th><td>' . htmlspecialchars($bgColor) . '</td></tr>';
echo '</table>';

echo '<h4>Widgets</h4>';
echo '<table class="table table-condensed table-bordered">';
echo '<tr><th>Hauptlink</th><td>' . htmlspecialchars($mainLink) . '</td></tr>';
echo '<tr><th>Bildliste</th><td>' . htmlspecialchars($imglist) . '</td></tr>';
echo '<tr><th>Linklist</th><td>' . htmlspecialchars($linklist) . '</td></tr>';
echo '<tr><th>Medialist</th><td>' . htmlspecialchars($medialist) . '</td></tr>';
echo '<tr><th>Multiple Links</th><td>';
if ($multiLinksArr) {
    echo '<ul style="margin:0;padding-left:1.2em">';
    foreach ($multiLinksArr as $ml) {
        echo '<li>' . htmlspecialchars((string) $ml) . '</li>';
    }
    echo '</ul>';
} else {
    echo '–';
}
echo '</td></tr>';
echo '</table>';

echo '<h4>Erweiterte Einstellungen</h4>';
echo '<table class="table table-condensed table-bordered">';
echo '<tr><th>Hinweistext</th><td>' . nl2br(htmlspecialchars($hint)) . '</td></tr>';
echo '<tr><th>Ausrichtung</th><td>' . htmlspecialchars($alignment) . '</td></tr>';
echo '</table>';

echo '<h4>Inhaltszeilen (' . count(MFormRepeaterHelper::filterEnabledItems($rows)) . ' aktive Einträge / ' . count($rows) . ' gesamt) <small>– copy_paste: true</small></h4>';
if ($rows) {
    foreach ($rows as $i => $row) {
        $isDisabled = is_array($row) ? !MFormRepeaterHelper::isItemEnabled($row) : false;
        echo '<div class="panel ' . ($isDisabled ? 'panel-default' : 'panel-primary') . '" style="margin-bottom:.5rem">';
        echo '<div class="panel-heading">Zeile ' . ($i + 1) . ($isDisabled ? ' <em>(deaktiviert)</em>' : '') . '</div>';
        echo '<div class="panel-body">';
        echo '<table class="table table-condensed" style="margin:0">';
        foreach (['title' => 'Titel', 'style' => 'Stil', 'text' => 'Text', 'cta_links' => 'CTA Links', 'downloads' => 'Downloads'] as $key => $label) {
            $val = is_array($row) && isset($row[$key]) ? (string) $row[$key] : '–';
            echo '<tr><th style="width:120px">' . htmlspecialchars($label) . '</th><td>' . htmlspecialchars($val ?: '–') . '</td></tr>';
        }
        echo '</table>';
        echo '</div></div>';
    }
} else {
    echo '<p class="text-muted">Keine Zeilen gespeichert.</p>';
}

echo '</div></div>';
