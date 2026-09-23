<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Alle drei Textareas liegen in Slot 1 (Punkt-Notation 1.1 - 1.3).
$values = MFormOutputHelper::values(1);
?>
<div class="collapse-output">
    <?php foreach (['1', '2', '3'] as $key): ?>
        <?php if ('' !== ($values[$key] ?? '')): ?>
            <div class="collapse-output__part">
                <?= nl2br(rex_escape((string) $values[$key])) ?>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>
</div>
