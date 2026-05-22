<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// --- Hex-Farbe oder CSS-Klasse korrekt anwenden ---
function applyColorValue(string $value, string $property = 'background'): string
{
    $value = trim($value);
    if ('' === $value) {
        return '';
    }
    if (str_starts_with($value, '.')) {
        // CSS-Klasse: Punkt entfernen
        return ' ' . ltrim($value, '.');
    }
    // Hex-Wert: als inline style
    return ' style="' . $property . ':' . rex_escape($value) . '"';
}

// REX_VALUE[id=1] = Hintergrundfarbe (Hex)
// REX_VALUE[id=2] = Textfarbe (Hex)
// REX_VALUE[id=3] = Hintergrundklasse (CSS)
// REX_VALUE[id=4] = Textfarbe / CSS-Klasse
// REX_VALUE[id=5] = Repeater (JSON)

$bg    = 'REX_VALUE[id=1]';
$color = 'REX_VALUE[id=2]';
$bgCss = 'REX_VALUE[id=3]';
$tcCss = 'REX_VALUE[id=4]';

// Hex-Werte als inline style
$bgStyle    = ('' !== $bg)    ? ' style="background:' . rex_escape($bg) . '"'    : '';
$colorStyle = ('' !== $color) ? ' style="color:'      . rex_escape($color) . '"' : '';

// CSS-Klassen (Punkt entfernen)
$bgClass = (str_starts_with($bgCss, '.')) ? ' ' . ltrim($bgCss, '.') : '';
$tcClass = (str_starts_with($tcCss, '.')) ? ' ' . ltrim($tcCss, '.') : '';

?>
<section class="demo-section<?= $bgClass ?>"<?= $bgStyle ?>>
    <p class="demo-text<?= $tcClass ?>"<?= $colorStyle ?>>
        Hintergrund: <code><?= rex_escape($bg) ?></code> &nbsp;|&nbsp;
        Textfarbe: <code><?= rex_escape($color) ?></code>
    </p>
</section>

<section class="demo-section<?= $bgClass ?>">
    <p class="<?= trim($tcClass) ?>">
        CSS-Klassen: Hintergrund <code><?= rex_escape($bgCss) ?></code> &nbsp;|&nbsp;
        Text <code><?= rex_escape($tcCss) ?></code>
    </p>
</section>

<?php
// Repeater: Inhaltskarten mit individuellen Farben
$cards = MFormRepeaterHelper::decode(5);
$cards = MFormRepeaterHelper::filterEnabledItems($cards);

foreach ($cards as $card):
    $cardBg = (string) ($card['bg'] ?? '');
    $cardTc = (string) ($card['text_color'] ?? '');

    $cardBgClass = str_starts_with($cardBg, '.') ? ' ' . ltrim($cardBg, '.') : '';
    $cardBgStyle = (!str_starts_with($cardBg, '.') && '' !== $cardBg) ? ' style="background:' . rex_escape($cardBg) . '"' : '';
    $cardTcClass = str_starts_with($cardTc, '.') ? ' ' . ltrim($cardTc, '.') : '';
?>
    <div class="card<?= $cardBgClass ?><?= $cardTcClass ?>"<?= $cardBgStyle ?>>
        <div class="card-body">
            <h3 class="card-title"><?= rex_escape((string) ($card['headline'] ?? '')) ?></h3>
            <div class="card-text"><?= nl2br(rex_escape((string) ($card['content'] ?? ''))) ?></div>
        </div>
    </div>
<?php endforeach; ?>
