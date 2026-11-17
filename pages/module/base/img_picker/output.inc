<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// REX_VALUE[id=1] = Inhaltstyp-Key (z. B. "image"), REX_VALUE[id=7] = Hinweis-Art, REX_VALUE[id=8] = Repeater
$type = 'REX_VALUE[id=1]';
$notice = 'REX_VALUE[id=7]';

echo '<p>Inhaltstyp: <strong>' . rex_escape($type) . '</strong>, Hinweis-Art: <strong>' . rex_escape($notice) . '</strong></p>';

echo '<ul>';
foreach (MFormRepeaterHelper::decode(8) as $item) {
    echo '<li>' . rex_escape((string) ($item['type'] ?? '')) . ': ' . rex_escape((string) ($item['title'] ?? '')) . '</li>';
}
echo '</ul>';
