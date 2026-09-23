<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Slot 1: die beiden Selects (1.0, 1.1).
// Slot 2: die Multiselects - deren Werte sind Arrays.
$selects = MFormOutputHelper::values(1);

$multi = MFormOutputHelper::value(2, 'multiselect', []);
if (!is_array($multi)) {
    $multi = [] === $multi || '' === $multi ? [] : [$multi];
}
?>
<div class="select-output">
    <?php if ('' !== (string) ($selects['0'] ?? '')): ?>
        <p>Auswahl 1: <?= rex_escape((string) $selects['0']) ?></p>
    <?php endif; ?>
    <?php if ('' !== (string) ($selects['1'] ?? '')): ?>
        <p>Auswahl 2: <?= rex_escape((string) $selects['1']) ?></p>
    <?php endif; ?>
    <?php if ([] !== $multi): ?>
        <ul>
            <?php foreach ($multi as $value): ?>
                <li><?= rex_escape((string) $value) ?></li>
            <?php endforeach; ?>
        </ul>
    <?php endif; ?>
</div>
