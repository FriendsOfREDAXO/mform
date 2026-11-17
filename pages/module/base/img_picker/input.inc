<?php

use FriendsOfRedaxo\MForm;

// addImgPickerField – kompakter Bild-Picker
// Gleiche Optionen wie addRadioImgField ('img' + 'label'), optional 'description' oder 'icon'.
// Im Formular steht nur die gewählte Option mit Vorschaubild; ein Klick öffnet alle Optionen als Kacheln.
// Gespeichert wird wie bei addSelectField der Array-Key – visible_if und Conditional Fieldsets funktionieren unverändert.

$svg = static fn (string $body): string => 'data:image/svg+xml;base64,' . base64_encode(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 80"><rect x="1" y="1" width="118" height="78" rx="6" fill="#fff" stroke="#c7ccd3"/>' . $body . '</svg>'
);
$lines = '<rect x="64" y="18" width="44" height="5" rx="2" fill="#68727d"/><rect x="64" y="28" width="36" height="4" rx="2" fill="#c7ccd3"/><rect x="64" y="36" width="30" height="4" rx="2" fill="#c7ccd3"/>';

$types = [
    'text' => [
        'label' => 'Text',
        'description' => 'Überschrift und Fließtext',
        'img' => $svg('<rect x="12" y="16" width="70" height="6" rx="2" fill="#68727d"/><rect x="12" y="30" width="96" height="4" rx="2" fill="#c7ccd3"/><rect x="12" y="38" width="90" height="4" rx="2" fill="#c7ccd3"/><rect x="12" y="46" width="94" height="4" rx="2" fill="#c7ccd3"/>'),
    ],
    'image' => [
        'label' => 'Bild + Text',
        'description' => 'Bild links, Text rechts',
        'img' => $svg('<rect x="10" y="14" width="46" height="36" rx="3" fill="#9fc3e3"/><path d="M10 50l18-16 11 9 17-14v21z" fill="#5b8fbf"/>' . $lines),
    ],
    'quote' => [
        'label' => 'Zitat',
        'description' => 'Großes Zitat mit Quelle',
        'img' => $svg('<text x="14" y="50" font-family="Georgia,serif" font-size="44" fill="#5bb585">“</text>' . $lines),
    ],
    'video' => [
        'label' => 'Video',
        'description' => 'YouTube oder MP4',
        'img' => $svg('<rect x="10" y="14" width="46" height="36" rx="3" fill="#324050"/><path d="M28 24l12 8-12 8z" fill="#fff"/>' . $lines),
    ],
];

// Variante mit Font-Icons statt Bildern
$icons = [
    'info' => ['icon' => 'rex-icon fa-circle-info', 'label' => 'Info', 'description' => 'Neutraler Hinweis'],
    'success' => ['icon' => 'rex-icon fa-circle-check', 'label' => 'Erfolg', 'description' => 'Etwas hat geklappt'],
    'warning' => ['icon' => 'rex-icon fa-triangle-exclamation', 'label' => 'Warnung', 'description' => 'Bitte beachten'],
];

echo MForm::factory()
    ->addFieldsetArea('Bild-Picker mit visible_if', MForm::factory()
        ->addImgPickerField(1, $types, ['label' => 'Inhaltstyp'], 'text')
        ->addTextField(2, ['label' => 'Überschrift'])
        ->addTextAreaField(3, ['label' => 'Text'])
            ->setVisibleIf(1, 'in', 'text,image')
        ->addMediaField(4, ['label' => 'Bild'])
            ->setVisibleIf(1, '=', 'image')
        ->addTextField(5, ['label' => 'Quelle des Zitats'])
            ->setVisibleIf(1, '=', 'quote')
        ->addTextField(6, ['label' => 'Video-URL'])
            ->setVisibleIf(1, '=', 'video'))
    ->addFieldsetArea('Bild-Picker mit Icons', MForm::factory()
        ->addImgPickerField(7, $icons, ['label' => 'Hinweis-Art'], 'info'))
    ->addFieldsetArea('Im Repeater', MForm::factory()
        ->addRepeaterElement(8, MForm::factory()
            ->addImgPickerField('type', $types, ['label' => 'Typ'], 'text')
            ->addTextField('title', ['label' => 'Titel']), true, true, ['btn_text' => 'Eintrag hinzufügen', 'item_title' => '{title}', 'default_count' => 1]))
    ->show();
