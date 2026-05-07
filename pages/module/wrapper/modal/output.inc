<?php
$headline = rex_var::toStr('REX_VALUE[1]');
$text = rex_var::toStr('REX_VALUE[2]');
$bgClass = rex_var::toStr('REX_VALUE[10]');
$paddingClass = rex_var::toStr('REX_VALUE[11]');
$fullwidth = rex_var::toStr('REX_VALUE[12]');

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
