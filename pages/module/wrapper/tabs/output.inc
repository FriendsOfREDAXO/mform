<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Die Felder liegen als Punkt-Notation in den Slots 1 und 2.
// values() liest solche Nicht-Repeater-Slots (decode() ist nur für Repeater).
$tab1 = MFormOutputHelper::values(1);
$tab2 = MFormOutputHelper::values(2);
?>
<div class="module-tabs">
    <?php if ('' !== ($tab1['1'] ?? '')): ?>
        <h2><?= rex_escape((string) $tab1['1']) ?></h2>
    <?php endif; ?>
    <?php if ('' !== ($tab2['1'] ?? '')): ?>
        <p><?= rex_escape((string) $tab2['1']) ?></p>
    <?php endif; ?>
    <?php if ('' !== ($tab2['2'] ?? '')): ?>
        <p><?= rex_escape((string) $tab2['2']) ?></p>
    <?php endif; ?>
</div>
