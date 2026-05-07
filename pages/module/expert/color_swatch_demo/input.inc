<?php

use FriendsOfRedaxo\MForm;

// Gemeinsame Swatch-Paletten als wiederverwendbare Arrays
$brandColors = [
    '#ffffff' => 'Weiß',
    '#f8f9fa' => 'Hellgrau',
    '#343a40' => 'Dunkelgrau',
    '#111111' => 'Schwarz',
    '#2f77bc' => 'Blau',
    '#27ae60' => 'Grün',
    '#e74c3c' => 'Rot',
    '#f39c12' => 'Orange',
    '#9b59b6' => 'Lila',
];

$cssUtilityColors = [
    '#ffffff' => 'Weiß',
    '#111111' => 'Schwarz',
    '.bg-primary'   => ['label' => 'Primär',       'preview' => '#0d6efd'],
    '.bg-secondary' => ['label' => 'Sekundär',      'preview' => '#6c757d'],
    '.bg-success'   => ['label' => 'Erfolg',        'preview' => '#198754'],
    '.bg-danger'    => ['label' => 'Gefahr',        'preview' => '#dc3545'],
    '.bg-warning'   => ['label' => 'Warnung',       'preview' => '#ffc107'],
    '.bg-info'      => ['label' => 'Info',          'preview' => '#0dcaf0'],
    '.bg-dark'      => ['label' => 'Dunkel',        'preview' => '#212529'],
    '.bg-light'     => ['label' => 'Hell',          'preview' => '#f8f9fa'],
];

$mform = MForm::factory()
    ->addTabElement('Farben', MForm::factory()

        ->addFieldsetArea('Direkter Hex-Farbwert', MForm::factory()
            // Klassischer Anwendungsfall: Hintergrundfarbe per Inline-Style
            ->addColorSwatchField(1, $brandColors, [
                'label'  => 'Hintergrundfarbe',
                'notice' => 'Gespeicherter Wert: Hex-Code (z. B. #2f77bc) – direkt als inline style nutzbar.',
            ], '#ffffff')

            // Textfarbe aus gleicher Palette
            ->addColorSwatchField(2, $brandColors, [
                'label'  => 'Textfarbe',
                'notice' => 'Gespeicherter Wert: Hex-Code – für color: REX_VALUE[id=2] im CSS.',
            ], '#111111')
        )

        ->addFieldsetArea('CSS-Utility-Klassen (Bootstrap)', MForm::factory()
            // CSS-Klassen-Variante: Wert ist ein Klassenname wie ".bg-primary"
            ->addColorSwatchField(3, $cssUtilityColors, [
                'label'  => 'Hintergrundklasse',
                'notice' => 'Gespeicherter Wert: CSS-Klasse (z. B. .bg-primary) – ltrim(".") im Template nutzen.',
            ])

            // Gemischte Palette: Hex + CSS-Klassen kombiniert
            ->addColorSwatchField(4, array_merge(
                ['#ffffff' => 'Weiß', '#000000' => 'Schwarz'],
                [
                    '.text-primary'   => ['label' => 'Primär',    'preview' => '#0d6efd'],
                    '.text-secondary' => ['label' => 'Sekundär',  'preview' => '#6c757d'],
                    '.text-success'   => ['label' => 'Erfolg',    'preview' => '#198754'],
                    '.text-danger'    => ['label' => 'Gefahr',    'preview' => '#dc3545'],
                    '.text-warning'   => ['label' => 'Warnung',   'preview' => '#ffc107'],
                    '.text-muted'     => ['label' => 'Gedämpft',  'preview' => '#6c757d'],
                ]
            ), ['label' => 'Textfarbe / CSS-Klasse'])
        )
    )

    ->addTabElement('Im Repeater', MForm::factory()

        ->addRepeaterElement(5, MForm::factory()
            ->addTextField('headline', ['label' => 'Überschrift'])
            ->addColorSwatchField('bg', $cssUtilityColors, ['label' => 'Hintergrundklasse'])
            ->addColorSwatchField('text_color', [
                '.text-white'   => ['label' => 'Weiß',     'preview' => '#ffffff'],
                '.text-dark'    => ['label' => 'Dunkel',   'preview' => '#212529'],
                '.text-primary' => ['label' => 'Primär',   'preview' => '#0d6efd'],
                '.text-muted'   => ['label' => 'Gedämpft', 'preview' => '#6c757d'],
            ], ['label' => 'Textfarbe'])
            ->addTextAreaField('content', ['label' => 'Inhalt'])
        , true, true, [
            'label'      => 'Inhaltskarten',
            'btn_text'   => 'Karte hinzufügen',
            'collapsed'  => true,
            'first_open' => true,
            'min'        => 1,
            'max'        => 10,
        ])
    )
;

echo $mform->show();
