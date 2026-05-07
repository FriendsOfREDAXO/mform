<?php

// REX_VALUE[id=1] = Read-Only Text (fest)
// REX_VALUE[id=2] = Read-Only Text (System-Info)
// REX_VALUE[id=3] = Read-Only Textarea
// REX_VALUE[id=4] = Sichtbares Textfeld
// REX_VALUE[id=5] = Hidden: Modul-Typ ("article_teaser")
// REX_VALUE[id=6] = Hidden: Erstellungszeitpunkt
// REX_VALUE[id=7] = Headline
// REX_VALUE[id=8] = Text
// REX_VALUE[id=9] = Hidden: Modul-Typ ("content-text")

$moduleType = 'REX_VALUE[id=9]';
$headline   = rex_escape('REX_VALUE[id=7]');
$text       = rex_escape('REX_VALUE[id=8]');
$createdAt  = rex_escape('REX_VALUE[id=6]');

// Typischer Anwendungsfall: Switch über Hidden-Feld-Typ
switch ($moduleType) {
    case 'content-text':
        echo '<article>';
        if ('' !== $headline) {
            echo '<h2>' . $headline . '</h2>';
        }
        if ('' !== $text) {
            echo '<p>' . $text . '</p>';
        }
        if ('' !== $createdAt) {
            echo '<small class="text-muted">Erstellt: ' . $createdAt . '</small>';
        }
        echo '</article>';
        break;

    default:
        // Fallback: Rohwerte ausgeben
        dump([
            'module_type' => $moduleType,
            'hidden_type' => 'REX_VALUE[id=5]',
            'created_at'  => $createdAt,
        ]);
        break;
}
