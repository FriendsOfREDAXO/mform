<?php

// REX_VALUE[id=1] = Layout-Key (z. B. "cards")
// REX_VALUE[id=2] = Ausrichtungs-Key (z. B. "center")
// REX_VALUE[id=3] = Größen-Key (z. B. "md")
// REX_VALUE[id=4] = Farb-Key (z. B. "primary")

$layout    = rex_escape('REX_VALUE[id=1]');
$align     = rex_escape('REX_VALUE[id=2]');
$size      = rex_escape('REX_VALUE[id=3]');
$colorKey  = rex_escape('REX_VALUE[id=4]');

// Farb-Key → CSS-Klasse oder Inline-Style
$colorClassMap = [
    'transparent' => '',
    'white'       => 'bg-white',
    'light'       => 'bg-light',
    'dark'        => 'bg-dark text-white',
    'black'       => 'bg-black text-white',
    'primary'     => 'bg-primary text-white',
    'success'     => 'bg-success text-white',
    'danger'      => 'bg-danger text-white',
    'warning'     => 'bg-warning',
    'info'        => 'bg-info',
];

$bgClass = $colorClassMap[$colorKey] ?? '';

// Größen-Key → CSS-Klasse
$sizeClassMap = ['sm' => 'fs-6', 'md' => 'fs-5', 'lg' => 'fs-4', 'xl' => 'fs-3'];
$sizeClass = $sizeClassMap[$size] ?? 'fs-5';

// Ausrichtungs-Key → CSS-Klasse
$alignClassMap = ['left' => 'text-start', 'center' => 'text-center', 'right' => 'text-end', 'justify' => 'text-justify'];
$alignClass = $alignClassMap[$align] ?? 'text-start';

?>
<div class="p-4 <?= $bgClass ?>">
    <p class="<?= $sizeClass ?> <?= $alignClass ?> mb-0">
        Demo-Inhalt – Layout: <strong><?= $layout ?></strong>,
        Ausrichtung: <strong><?= $align ?></strong>,
        Größe: <strong><?= $size ?></strong>,
        Hintergrund: <strong><?= $colorKey ?></strong>
    </p>
</div>
