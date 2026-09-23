<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Slot 1: Radio (1.1) + zwei Textareas (1.2, 1.3).
// Slot 2: Checkbox (2.1) und Toggle-Checkbox (2.4) mit ihren Feldern.
$radioGroup = MFormOutputHelper::values(1);
$checkGroup = MFormOutputHelper::values(2);

$radio = (string) ($radioGroup['1'] ?? '');
?>
<div class="collapse-checkradio-output">
    <?php // Radio steuert, welche Textarea ausgegeben wird. ?>
    <?php if ('' !== $radio && '' !== ($radioGroup[$radio] ?? '')): ?>
        <div><?= nl2br(rex_escape((string) $radioGroup[$radio])) ?></div>
    <?php endif; ?>

    <?php // Checkbox 2.1 schaltet Text + Link frei. ?>
    <?php if ('' !== (string) ($checkGroup['1'] ?? '')): ?>
        <p><?= rex_escape((string) ($checkGroup['2'] ?? '')) ?></p>
        <?php if ('' !== ($checkGroup['3'] ?? '')): ?>
            <a href="<?= rex_escape(rex_getUrl((string) $checkGroup['3'])) ?>">Mehr erfahren</a>
        <?php endif; ?>
    <?php endif; ?>

    <?php // Toggle-Checkbox 2.4 schaltet Text + Link frei. ?>
    <?php if ('' !== (string) ($checkGroup['4'] ?? '')): ?>
        <p><?= rex_escape((string) ($checkGroup['5'] ?? '')) ?></p>
        <?php if ('' !== ($checkGroup['6'] ?? '')): ?>
            <a href="<?= rex_escape(rex_getUrl((string) $checkGroup['6'])) ?>">Mehr erfahren</a>
        <?php endif; ?>
    <?php endif; ?>
</div>
