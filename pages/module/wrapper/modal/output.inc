<?php
// REX_VALUE[n] wird vom REDAXO-Parser vor der Ausführung durch den Slot-Wert ersetzt.
$headline = 'REX_VALUE[1]';
$text = 'REX_VALUE[2]';
$bgClass = 'REX_VALUE[10]';
$paddingClass = 'REX_VALUE[11]';
$fullwidth = 'REX_VALUE[12]';

$containerClass = 'container';
if ($fullwidth === '1') {
    $containerClass = 'container-fluid';
}

$sectionClass = trim($bgClass . ' ' . $paddingClass);
?>
<section class="<?= htmlspecialchars($sectionClass) ?>">
    <div class="<?= htmlspecialchars($containerClass) ?>">
        <?php if ($headline): ?>
            <h2><?= htmlspecialchars($headline) ?></h2>
        <?php endif; ?>
        <?php if ($text): ?>
            <p><?= nl2br(htmlspecialchars($text)) ?></p>
        <?php endif; ?>
    </div>
</section>
