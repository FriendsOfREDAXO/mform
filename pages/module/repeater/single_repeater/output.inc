<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// Slot 1 ist ein normales Textfeld, Slot 2 der Repeater.
// decode() liefert die Items bereits ohne deaktivierte Einträge.
// REX_VALUE[1] ist bereits per rex_escape() + nl2br() aufbereitet - nicht erneut maskieren.
$headline = 'REX_VALUE[1]';
$items = MFormRepeaterHelper::decode(2);
?>
<section class="single-repeater">
    <?php if ('' !== $headline): ?>
        <h2><?= $headline ?></h2>
    <?php endif; ?>

    <?php if ([] !== $items): ?>
    <ul>
        <?php foreach ($items as $item): ?>
        <li>
            <strong><?= rex_escape((string) ($item['text1'] ?? '')) ?></strong>
            <?php if ('' !== (string) ($item['radio1'] ?? '')): ?>
                <span class="badge">Option <?= rex_escape((string) $item['radio1']) ?></span>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>
</section>
