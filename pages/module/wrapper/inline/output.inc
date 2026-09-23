<?php

use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Zwei Inline-Gruppen: Slot 1 (1.1, 1.2) und Slot 2 (2.1, 2.2).
$group1 = MFormOutputHelper::values(1);
$group2 = MFormOutputHelper::values(2);
?>
<div class="inline-output">
    <p><?= rex_escape(trim(($group1['1'] ?? '') . ' ' . ($group1['2'] ?? ''))) ?></p>
    <p><?= rex_escape(trim(($group2['1'] ?? '') . ' ' . ($group2['2'] ?? ''))) ?></p>
</div>
