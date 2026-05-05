<?php
/**
 * Demo: Conditional Fields – Output
 *
 * REX_VALUE[1]  – Content-Typ (text | image | video)
 * REX_VALUE[2]  – Erweiterte Optionen aktiv (1 | '')
 * REX_VALUE[3]  – Text-Headline
 * REX_VALUE[4]  – Text-Inhalt
 * REX_VALUE[5]  – Hauptbild (Dateiname)
 * REX_VALUE[6]  – Alt-Text
 * REX_VALUE[7]  – Video-URL
 * REX_VALUE[8]  – Autoplay (1 | '')
 * REX_VALUE[9]  – CSS-Klasse
 * REX_VALUE[10] – Anker-ID
 */

$type      = 'REX_VALUE[1]';
$extended  = 'REX_VALUE[2]';
$headline  = 'REX_VALUE[3]';
$text      = 'REX_VALUE[4]';
$image     = 'REX_MEDIA[id=5]';
$altText   = 'REX_VALUE[6]';
$videoUrl  = 'REX_VALUE[7]';
$autoplay  = 'REX_VALUE[8]';
$cssClass  = 'REX_VALUE[9]';
$anchorId  = 'REX_VALUE[10]';

// Anker/CSS nur ausgeben wenn gesetzt
$idAttr    = $anchorId  ? ' id="' . rex_escape($anchorId)  . '"' : '';
$classAttr = $cssClass  ? ' class="' . rex_escape($cssClass) . '"' : '';

echo '<section' . $idAttr . $classAttr . '>';

switch ($type) {
    case 'text':
        if ($headline) {
            echo '<h2>' . rex_escape($headline) . '</h2>';
        }
        if ($text) {
            echo '<div class="text-content">' . $text . '</div>';
        }
        break;

    case 'image':
        if ($image) {
            echo '<figure>';
            echo '<img src="' . rex_url::media($image) . '" alt="' . rex_escape($altText) . '">';
            echo '</figure>';
        }
        break;

    case 'video':
        if ($videoUrl) {
            $autoplayParam = $autoplay ? '?autoplay=1' : '';
            // Einfaches YouTube-Embed (Kurzform)
            $embedUrl = preg_replace(
                '%(?:https?://)?(?:www\.)?youtu(?:\.be/|be\.com/watch\?v=)([a-zA-Z0-9_-]+).*%',
                'https://www.youtube-nocookie.com/embed/$1' . $autoplayParam,
                $videoUrl
            );
            echo '<div class="video-wrapper">';
            echo '<iframe src="' . rex_escape($embedUrl) . '" allowfullscreen loading="lazy"></iframe>';
            echo '</div>';
        }
        break;
}

echo '</section>';
