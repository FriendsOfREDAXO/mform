<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// Slot 1 enthält den Repeater mit Custom-Link-Feldern.
// Custom-Link liefert eine Artikel-ID bzw. eine externe URL als String;
// rex_getUrl() löst interne IDs auf und lässt externe Werte unverändert.
$items = MFormRepeaterHelper::decode(1);

$linkUrl = static function (mixed $value): string {
    $value = trim((string) $value);
    if ('' === $value) {
        return '';
    }
    return ctype_digit($value) ? rex_getUrl($value) : $value;
};
?>
<section class="widgets-repeater">
    <?php foreach ($items as $item): ?>
    <div class="widgets-repeater__item">
        <?php $url = $linkUrl($item['custom_link'] ?? ''); ?>
        <?php if ('' !== $url): ?>
            <a href="<?= rex_escape($url) ?>"><?= rex_escape($url) ?></a>
        <?php endif; ?>

        <?php $children = $item['fields1'] ?? []; ?>
        <?php if (is_array($children) && [] !== $children): ?>
        <ul>
            <?php foreach ($children as $child): ?>
                <?php $childUrl = $linkUrl($child['custom_link'] ?? ''); ?>
                <?php if ('' !== $childUrl): ?>
                    <li><a href="<?= rex_escape($childUrl) ?>"><?= rex_escape($childUrl) ?></a></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</section>
