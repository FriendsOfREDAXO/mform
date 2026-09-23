<?php
// Zwei einfache Slots, keine Punkt-Notation -> direkt als REX_VALUE nutzbar.
// REDAXO liefert den Wert bereits per rex_escape() + nl2br() aufbereitet - nicht erneut maskieren.
$left  = 'REX_VALUE[1]';
$right = 'REX_VALUE[2]';
?>
<div class="row">
    <div class="col-sm-6"><?= $left ?></div>
    <div class="col-sm-6"><?= $right ?></div>
</div>
