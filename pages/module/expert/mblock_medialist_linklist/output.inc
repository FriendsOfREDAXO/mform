<?php

use FriendsOfRedaxo\MBlock\MBlock;

if (!class_exists(MBlock::class)) {
    return;
}

$items = MBlock::getOnlineDataArray('REX_VALUE[id=1]');

if (!is_array($items) || [] === $items) {
    echo '<p>Keine Eintraege vorhanden.</p>';
    return;
}

echo '<div class="alert alert-info">Ausgabe zur schnellen Feldpruefung (medialist/linklist/imagelist).</div>';

foreach ($items as $item) {
    echo '<h4>' . rex_escape((string) ($item['headline'] ?? 'Ohne Titel')) . '</h4>';

    echo '<ul>';
    echo '<li><strong>medialist:</strong> ' . rex_escape((string) ($item['medialist'] ?? '')) . '</li>';
    echo '<li><strong>linklist:</strong> ' . rex_escape((string) ($item['linklist'] ?? '')) . '</li>';
    echo '<li><strong>imagelist:</strong> ' . rex_escape((string) ($item['imagelist'] ?? '')) . '</li>';
    echo '</ul>';
}
