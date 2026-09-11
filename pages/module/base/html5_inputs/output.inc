<?php
$v = rex_var::toArray('REX_VALUE[1]') ?: [];
?>
<ul>
    <li>Menge: <?= rex_escape((string) ($v['menge'] ?? '')) ?></li>
    <li>Deckkraft: <?= rex_escape((string) ($v['opacity'] ?? '')) ?> %</li>
    <li>Beginn: <?= rex_escape((string) ($v['start'] ?? '')) ?></li>
    <li>Termin: <?= rex_escape((string) ($v['termin'] ?? '')) ?></li>
    <li>Uhrzeit: <?= rex_escape((string) ($v['uhrzeit'] ?? '')) ?></li>
    <li>E-Mail: <?= rex_escape((string) ($v['mail'] ?? '')) ?></li>
    <li>Farbe: <span style="display:inline-block;width:1em;height:1em;background:<?= rex_escape((string) ($v['farbe'] ?? '')) ?>"></span> <?= rex_escape((string) ($v['farbe'] ?? '')) ?></li>
</ul>
