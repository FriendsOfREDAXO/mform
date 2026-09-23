<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// Slot 2 enthält den Repeater der Ebene 1. Der verschachtelte Repeater
// ("fields1") steht als Array im jeweiligen Item - decode() bereitet ihn
// rekursiv auf, deaktivierte Items sind bereits entfernt.
// REX_VALUE[1] ist bereits per rex_escape() + nl2br() aufbereitet - nicht erneut maskieren.
$headline = 'REX_VALUE[1]';
$items = MFormRepeaterHelper::decode(2);
?>
<section class="nested-repeater">
    <?php if ('' !== $headline): ?>
        <h2><?= $headline ?></h2>
    <?php endif; ?>

    <?php foreach ($items as $item): ?>
    <div class="nested-repeater__level1">
        <h3><?= rex_escape((string) ($item['text1'] ?? '')) ?></h3>
        <?php if ('' !== (string) ($item['radio1'] ?? '')): ?>
            <p>Option <?= rex_escape((string) $item['radio1']) ?></p>
        <?php endif; ?>

        <?php $children = $item['fields1'] ?? []; ?>
        <?php if (is_array($children) && [] !== $children): ?>
        <ul class="nested-repeater__level2">
            <?php foreach ($children as $child): ?>
            <li>
                <?= rex_escape((string) ($child['text2'] ?? '')) ?>
                <?php if ('' !== (string) ($child['radio2'] ?? '')): ?>
                    <span class="badge">Option <?= rex_escape((string) $child['radio2']) ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</section>
