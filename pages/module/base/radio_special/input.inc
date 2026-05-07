<?php

use FriendsOfRedaxo\MForm;

// Radio-Varianten: Img, Icon, Color
// addRadioImgField  – visuelle Auswahl per Bild-Vorschau
// addRadioIconField – visuelle Auswahl per Icon (z. B. FontAwesome)
// addRadioColorField – visuelle Auswahl per Farbfeld

// --- Bild-Optionen für addRadioImgField ---
$layoutOptions = [
    'cards'      => ['img' => 'https://picsum.photos/seed/layout-cards/280/180',      'label' => 'Cards'],
    'hero'       => ['img' => 'https://picsum.photos/seed/layout-hero/280/180',       'label' => 'Hero-Banner'],
    'split'      => ['img' => 'https://picsum.photos/seed/layout-split/280/180',      'label' => 'Split (50/50)'],
    'fullwidth'  => ['img' => 'https://picsum.photos/seed/layout-full/280/180',       'label' => 'Vollbreite'],
    'sidebar'    => ['img' => 'https://picsum.photos/seed/layout-sidebar/280/180',    'label' => 'Sidebar rechts'],
    'masonry'    => ['img' => 'https://picsum.photos/seed/layout-masonry/280/180',    'label' => 'Masonry-Raster'],
];

// --- Icon-Optionen für addRadioIconField ---
// Jede Option: ['icon' => 'CSS-Klasse', 'label' => 'Bezeichnung']
$alignOptions = [
    'left'   => ['icon' => 'fa fa-align-left',    'label' => 'Links'],
    'center' => ['icon' => 'fa fa-align-center',  'label' => 'Zentriert'],
    'right'  => ['icon' => 'fa fa-align-right',   'label' => 'Rechts'],
    'justify'=> ['icon' => 'fa fa-align-justify',  'label' => 'Blocksatz'],
];

$sizeOptions = [
    'sm' => ['icon' => 'rex-icon fa-font',                'label' => 'Klein'],
    'md' => ['icon' => 'rex-icon fa-font',                'label' => 'Mittel'],
    'lg' => ['icon' => 'rex-icon fa-font',                'label' => 'Groß'],
    'xl' => ['icon' => 'rex-icon fa-font',                'label' => 'XL'],
];

// --- Farb-Optionen für addRadioColorField ---
$colorOptions = [
    'transparent' => ['color' => 'transparent', 'label' => 'Transparent'],
    'white'       => ['color' => '#ffffff',      'label' => 'Weiß'],
    'light'       => ['color' => '#f8f9fa',      'label' => 'Hellgrau'],
    'dark'        => ['color' => '#343a40',      'label' => 'Dunkelgrau'],
    'black'       => ['color' => '#000000',      'label' => 'Schwarz'],
    'primary'     => ['color' => '#0d6efd',      'label' => 'Primär'],
    'success'     => ['color' => '#198754',      'label' => 'Erfolg'],
    'danger'      => ['color' => '#dc3545',      'label' => 'Gefahr'],
    'warning'     => ['color' => '#ffc107',      'label' => 'Warnung'],
    'info'        => ['color' => '#0dcaf0',      'label' => 'Info'],
];

echo MForm::factory()
    ->addTabElement('Layout-Auswahl (Bild)', MForm::factory()
        ->addFieldsetArea('addRadioImgField – Layout-Typ', MForm::factory()
            ->addRadioImgField(1, $layoutOptions, [
                'label'  => 'Layout',
                'notice' => 'Gespeichert wird der Array-Key (z. B. "cards"). Bilder können lokale Media-URLs oder externe URLs sein.',
            ], 'cards')
        )
    )
    ->addTabElement('Ausrichtung & Größe (Icon)', MForm::factory()
        ->addFieldsetArea('addRadioIconField – Textausrichtung', MForm::factory()
            ->addRadioIconField(2, $alignOptions, [
                'label'  => 'Textausrichtung',
                'notice' => 'Gespeichert wird der Array-Key (z. B. "center"). Icons werden über CSS-Klassen eingebunden (FontAwesome, REDAXO-Icons etc.).',
            ], 'left')
        )
        ->addFieldsetArea('addRadioIconField – Schriftgröße', MForm::factory()
            ->addRadioIconField(3, $sizeOptions, [
                'label'  => 'Schriftgröße',
            ], 'md')
        )
    )
    ->addTabElement('Hintergrundfarbe (Farbfeld)', MForm::factory()
        ->addFieldsetArea('addRadioColorField – Hintergrundfarbe', MForm::factory()
            ->addRadioColorField(4, $colorOptions, [
                'label'  => 'Hintergrundfarbe',
                'notice' => 'Gespeichert wird der Array-Key (z. B. "primary"). Im Template dann als CSS-Klasse oder Inline-Style verwenden.',
            ], 'white')
        )
    )
    ->show();
