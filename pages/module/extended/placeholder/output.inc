<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Beide Textfelder liegen in Slot 1 (1.0 und 1.1).
$values = MFormOutputHelper::values(1);
?>
<div class="placeholder-output">
    <?php if ('' !== ($values['0'] ?? '')): ?>
        <h2><?= rex_escape((string) $values['0']) ?></h2>
    <?php endif; ?>
    <?php if ('' !== ($values['1'] ?? '')): ?>
        <p><?= rex_escape((string) $values['1']) ?></p>
    <?php endif; ?>
</div>
