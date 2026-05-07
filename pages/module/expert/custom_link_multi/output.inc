<?php

// REX_VALUE[id=1] = Einzelner Custom-Link-String
// REX_VALUE[id=2] = Mehrere Links (JSON-Array)
// REX_VALUE[id=3] = Download-Links (JSON-Array)

// --- Einzelner Link ---
$singleLink = 'REX_VALUE[id=1]';
if ('' !== $singleLink) {
    // Custom-Link-String auflösen
    $url = rex_getUrl('', '', [], '&');
    // Besser: MForm stellt keinen eigenen Resolver bereit –
    // der Wert ist direkt verwendbar als href-Attribut (intern: rex://article/12, extern: https://...)
    // Im einfachsten Fall: direkt als href, REDAXO löst rex:// URIs im Frontend auf.
    echo '<a href="' . rex_escape($singleLink) . '" class="btn btn-primary">CTA-Button</a>';
}

// --- Link-Liste (mehrere Links) ---
$linksJson = 'REX_VALUE[id=2]';
if ('' !== $linksJson) {
    $links = json_decode($linksJson, true);
    if (is_array($links) && count($links) > 0) {
        echo '<ul class="list-unstyled">';
        foreach ($links as $link) {
            $href = rex_escape((string) $link);
            if ('' !== $href) {
                echo '<li><a href="' . $href . '">' . $href . '</a></li>';
            }
        }
        echo '</ul>';
    }
}

// --- Download-Links ---
$downloadsJson = 'REX_VALUE[id=3]';
if ('' !== $downloadsJson) {
    $downloads = json_decode($downloadsJson, true);
    if (is_array($downloads) && count($downloads) > 0) {
        echo '<ul class="list-unstyled">';
        foreach ($downloads as $dl) {
            $href = rex_escape((string) $dl);
            // Dateiname aus dem Pfad extrahieren
            $filename = rex_escape(basename((string) $dl));
            if ('' !== $href) {
                echo '<li><a href="' . $href . '" download>' . $filename . '</a></li>';
            }
        }
        echo '</ul>';
    }
}
