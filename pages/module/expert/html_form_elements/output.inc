<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Alle HTML5-Felder des Moduls liegen in Slot 1 (Punkt-Notation 1.0, 1.1, ...).
$values = MFormOutputHelper::values(1);
?>
<dl class="html-form-elements-output">
    <?php foreach ($values as $key => $value): ?>
        <?php if (is_array($value) || '' === (string) $value) { continue; } ?>
        <dt><?= rex_escape((string) $key) ?></dt>
        <dd><?= rex_escape((string) $value) ?></dd>
    <?php endforeach; ?>
</dl>
