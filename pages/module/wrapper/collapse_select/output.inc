<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Slot 1 enthält das Select ("select"), Slot 2 die drei Textareas (2.1 - 2.3).
$values = MFormOutputHelper::values(2);

// Der Select-Wert entscheidet, welcher Collapse-Bereich ausgegeben wird.
// value() holt ein einzelnes Feld direkt aus dem Slot.
$selected = (string) MFormOutputHelper::value(1, 'select', '');
?>
<?php if ('' !== $selected && '0' !== $selected && '' !== ($values[$selected] ?? '')): ?>
<div class="collapse-select-output">
    <?= nl2br(rex_escape((string) $values[$selected])) ?>
</div>
<?php endif; ?>
