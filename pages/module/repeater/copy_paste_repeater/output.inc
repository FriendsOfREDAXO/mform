<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// REX_VALUE[id=1] = Flex-Repeater-Daten (JSON)
// REX_VALUE[id=2] = Einfacher Flex-Repeater (JSON)

// --- Flex Repeater mit Copy/Paste ---
$items = MFormRepeaterHelper::decode('REX_VALUE[id=1]');

if (!empty($items)) {
    echo '<ul class="list-group mb-4">';
    foreach ($items as $item) {
        $title    = rex_escape((string) ($item['title'] ?? ''));
        $text     = rex_escape((string) ($item['text'] ?? ''));
        $category = rex_escape((string) ($item['category'] ?? ''));
        $active   = !empty($item['active']);
        $image    = rex_escape((string) ($item['image'] ?? ''));

        if (!$active) {
            continue; // Inaktive Items überspringen
        }

        echo '<li class="list-group-item">';
        if ($image) {
            echo '<img src="' . rex_url::media($image) . '" alt="" class="img-thumbnail float-end ms-2" style="max-width:80px">';
        }
        echo '<strong>' . $title . '</strong>';
        if ($category) {
            echo ' <span class="badge bg-secondary">' . $category . '</span>';
        }
        if ($text) {
            echo '<p class="mb-0 mt-1">' . $text . '</p>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

// --- Einfacher Flex Repeater ---
$simpleItems = MFormRepeaterHelper::decode('REX_VALUE[id=2]');

if (!empty($simpleItems)) {
    echo '<ul class="list-unstyled">';
    foreach ($simpleItems as $item) {
        $name = rex_escape((string) ($item['name'] ?? ''));
        $link = (string) ($item['link'] ?? '');

        if ('' !== $name) {
            echo '<li>';
            if ('' !== $link) {
                echo '<a href="' . rex_escape($link) . '">' . $name . '</a>';
            } else {
                echo $name;
            }
            echo '</li>';
        }
    }
    echo '</ul>';
}
