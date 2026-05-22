<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

/**
 * Template-API-Demo: Ausgabe
 *
 * REX_VALUE-Mapping (entspricht den id_prefix-Werten im Input):
 *   main_title, main_subtitle, main_image, main_link, main_bg
 *   side_title, side_subtitle, side_image, side_link
 *   seo_title, seo_description, seo_og_image, seo_canonical
 *   REX_VALUE[id=99] = Repeater (Teaser-Kacheln)
 */

// Hilfsfunktion: Farbwert (Hex oder CSS-Klasse) als class/style aufbereiten
function resolveColor(string $value): array
{
    $value = trim($value);
    if ('' === $value) {
        return ['class' => '', 'style' => ''];
    }
    if (str_starts_with($value, '.')) {
        return ['class' => ' ' . ltrim($value, '.'), 'style' => ''];
    }
    return ['class' => '', 'style' => ' style="background:' . rex_escape($value) . '"'];
}

// --- Haupt-Teaser ---
$mainBg = resolveColor('REX_VALUE[main_bg]');
?>

<section class="teaser teaser--main<?= $mainBg['class'] ?>"<?= $mainBg['style'] ?>>
    <?php if ('REX_VALUE[main_image]' !== ''): ?>
    <img src="<?= rex_url::media('REX_VALUE[main_image]') ?>"
         alt="<?= rex_escape('REX_VALUE[main_title]') ?>"
         class="teaser__image">
    <?php endif; ?>
    <div class="teaser__body">
        <h2 class="teaser__title"><?= rex_escape('REX_VALUE[main_title]') ?></h2>
        <?php if ('' !== 'REX_VALUE[main_subtitle]'): ?>
        <p class="teaser__subtitle"><?= rex_escape('REX_VALUE[main_subtitle]') ?></p>
        <?php endif; ?>
        <?php if ('' !== 'REX_VALUE[main_link]'): ?>
        <a href="<?= rex_escape('REX_VALUE[main_link]') ?>" class="btn btn-primary">Mehr erfahren</a>
        <?php endif; ?>
    </div>
</section>

<!-- Neben-Teaser (ohne Farbe) -->
<?php if ('' !== 'REX_VALUE[side_title]'): ?>
<section class="teaser teaser--side">
    <div class="teaser__body">
        <h3 class="teaser__title"><?= rex_escape('REX_VALUE[side_title]') ?></h3>
        <?php if ('' !== 'REX_VALUE[side_subtitle]'): ?>
        <p class="teaser__subtitle"><?= rex_escape('REX_VALUE[side_subtitle]') ?></p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php
// --- SEO (nur im <head> sinnvoll – hier als Debug-Ausgabe) ---
$seoTitle       = 'REX_VALUE[seo_title]';
$seoDescription = 'REX_VALUE[seo_description]';
$seoOgImage     = 'REX_VALUE[seo_og_image]';
$seoCanonical   = 'REX_VALUE[seo_canonical]';

// In einem echten Modul würden diese Werte per Extension Point in den <head> eingefügt,
// z.B. über das meta_info Addon oder einen eigenen EP.
// Hier als Kommentar-Ausgabe zur Demonstration:
?>
<!-- SEO-Meta (würde im <head> stehen):
    <title><?= rex_escape($seoTitle) ?></title>
    <meta name="description" content="<?= rex_escape($seoDescription) ?>">
    <?php if ('' !== $seoOgImage): ?>
    <meta property="og:image" content="<?= rex_url::media($seoOgImage) ?>">
    <?php endif; ?>
    <?php if ('' !== $seoCanonical): ?>
    <link rel="canonical" href="<?= rex_escape($seoCanonical) ?>">
    <?php endif; ?>
-->

<?php
// --- Repeater: Teaser-Kacheln ---
$cards = MFormRepeaterHelper::decode(99);
$cards = MFormRepeaterHelper::filterEnabledItems($cards);

if ([] !== $cards):
?>
<div class="teaser-grid">
    <?php foreach ($cards as $card):
        $bg = resolveColor((string) ($card['bg'] ?? ''));
    ?>
    <div class="teaser-grid__item<?= $bg['class'] ?>"<?= $bg['style'] ?>>
        <?php if (!empty($card['image'])): ?>
        <img src="<?= rex_url::media((string) $card['image']) ?>"
             alt="<?= rex_escape((string) ($card['title'] ?? '')) ?>"
             class="teaser-grid__image">
        <?php endif; ?>
        <div class="teaser-grid__body">
            <h3><?= rex_escape((string) ($card['title'] ?? '')) ?></h3>
            <?php if (!empty($card['subtitle'])): ?>
            <p><?= rex_escape((string) $card['subtitle']) ?></p>
            <?php endif; ?>
            <?php if (!empty($card['link'])): ?>
            <a href="<?= rex_escape((string) $card['link']) ?>">Mehr</a>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
