<?php
// Zwei einfache Slots, keine Punkt-Notation -> direkt als REX_VALUE nutzbar.
$left  = 'REX_VALUE[1]';
$right = 'REX_VALUE[2]';
?>
<div class="row">
    <div class="col-sm-6"><?= rex_escape($left) ?></div>
    <div class="col-sm-6"><?= rex_escape($right) ?></div>
</div>
