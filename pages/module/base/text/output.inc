<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Slot 1 bündelt die Text-/Textarea-Felder (1.0 - 1.4),
// Slot 2 die Read-Only-Felder (2.0, 2.1).
$texts    = MFormOutputHelper::values(1);
$readOnly = MFormOutputHelper::values(2);
?>
<div class="text-output">
    <?php if ('' !== ($texts['0'] ?? '')): ?>
        <h2><?= rex_escape((string) $texts['0']) ?></h2>
    <?php endif; ?>
    <?php foreach (['2', '3', '4'] as $key): ?>
        <?php if ('' !== ($texts[$key] ?? '')): ?>
            <p><?= nl2br(rex_escape((string) $texts[$key])) ?></p>
        <?php endif; ?>
    <?php endforeach; ?>
    <?php if ('' !== ($readOnly['0'] ?? '')): ?>
        <p class="text-muted"><?= rex_escape((string) $readOnly['0']) ?></p>
    <?php endif; ?>
</div>
