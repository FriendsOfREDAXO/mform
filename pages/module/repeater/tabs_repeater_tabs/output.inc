<?php

$headline = 'REX_VALUE[1]';
$sectionsRaw = html_entity_decode('REX_VALUE[2]', ENT_QUOTES | ENT_HTML5, 'UTF-8');
$sections = json_decode($sectionsRaw, true);

if (!is_array($sections)) {
    $sections = [];
}

echo '<div class="panel panel-default" style="margin:1rem">';
echo '<div class="panel-heading"><strong>Tabs -> Repeater im Tab -> Tabs (Demo)</strong></div>';
echo '<div class="panel-body">';
echo '<p><strong>Headline:</strong> ' . htmlspecialchars($headline) . '</p>';
echo '<p><strong>Sektionen:</strong> ' . count($sections) . '</p>';

if ([] === $sections) {
    echo '<p class="text-muted">Keine Sektionen gespeichert.</p>';
} else {
    foreach ($sections as $sectionIndex => $section) {
        $sectionLabel = is_array($section) && isset($section['section_label']) ? (string) $section['section_label'] : '';
        $sectionStyle = is_array($section) && isset($section['style']) ? (string) $section['style'] : 'default';
        $blocks = is_array($section) && isset($section['blocks']) && is_array($section['blocks']) ? $section['blocks'] : [];

        echo '<div class="panel panel-info" style="margin-bottom:10px">';
        echo '<div class="panel-heading">Sektion ' . ($sectionIndex + 1) . ': ' . htmlspecialchars($sectionLabel ?: 'Ohne Titel') . '</div>';
        echo '<div class="panel-body">';
        echo '<p><strong>Stil:</strong> ' . htmlspecialchars($sectionStyle) . '</p>';
        echo '<p><strong>Blöcke:</strong> ' . count($blocks) . '</p>';

        if ([] !== $blocks) {
            echo '<ul>';
            foreach ($blocks as $block) {
                $blockTitle = is_array($block) && isset($block['title']) ? (string) $block['title'] : '';
                $blockLink = is_array($block) && isset($block['link']) ? (string) $block['link'] : '';
                echo '<li><strong>' . htmlspecialchars($blockTitle ?: 'Ohne Titel') . '</strong>';
                if ('' !== $blockLink) {
                    echo ' <small>(' . htmlspecialchars($blockLink) . ')</small>';
                }
                echo '</li>';
            }
            echo '</ul>';
        }

        echo '</div></div>';
    }
}

echo '</div></div>';
