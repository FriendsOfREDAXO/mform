<?php

use FriendsOfRedaxo\MForm;

$layoutOptions = [
    'cards' => ['img' => 'https://picsum.photos/seed/mform-cards/280/180', 'label' => 'Cards'],
    'hero' => ['img' => 'https://picsum.photos/seed/mform-hero/280/180', 'label' => 'Hero'],
    'split' => ['img' => 'https://picsum.photos/seed/mform-split/280/180', 'label' => 'Split'],
];

$rowForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addSelectField('style', ['neutral' => 'Neutral', 'primary' => 'Primary', 'warning' => 'Warning'], ['label' => 'Stil'])
    ->addTextAreaField('text', ['label' => 'Text (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addLinklistField('cta_links', ['category' => 1], null, ['label' => 'CTA Links (Linklist)'])
    ->addMedialistField('downloads', ['types' => 'pdf,zip,jpg,png'], null, ['label' => 'Downloads (Medialist)'])
;

$mform = MForm::factory()
    ->addFieldsetArea('Grunddaten', MForm::factory()
        ->addTextField(1, ['label' => 'Modul-Headline'])
        ->addSelectField(2, ['de' => 'Deutsch', 'en' => 'English'], ['label' => 'Sprache'])
        ->addToggleCheckboxField(3, [1 => 'Feature aktivieren'], ['label' => 'Feature'])
    )
    ->addFieldsetArea('Darstellung', MForm::factory()
        ->addRadioImgField(4, $layoutOptions, ['label' => 'Layout'], 'cards')
        ->addRadioColorField(5, [
            'transparent' => ['color' => 'transparent', 'label' => 'Transparent'],
            'white' => ['color' => '#ffffff', 'label' => 'Weiss'],
            'black' => ['color' => '#111111', 'label' => 'Dunkel'],
            'blue' => ['color' => '#2f77bc', 'label' => 'Blau'],
        ], ['label' => 'Hintergrundfarbe'], 'transparent')
    )
    ->addFieldsetArea('Widgets', MForm::factory()
        ->addCustomLinkField(6, ['label' => 'Hauptlink', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
        ->addImagelistField(7, ['label' => 'Bildliste'])
        ->addLinklistField(8, ['category' => 1], null, ['label' => 'Verwandte Artikel'])
        ->addMedialistField(9, ['types' => 'pdf,zip,jpg,png'], null, ['label' => 'Dateiliste'])
    )
    ->addCollapseElement('Erweiterte Einstellungen', MForm::factory()
        ->addTextAreaField(10, ['label' => 'Hinweistext'])
        ->addSelectField(11, ['left' => 'Links', 'center' => 'Zentriert', 'right' => 'Rechts'], ['label' => 'Ausrichtung'])
    , true)
    ->addRepeaterElement(12, $rowForm, true, true, [
        'label' => 'Inhaltszeilen',
        'btn_text' => 'Zeile hinzufuegen',
        'collapsed' => true,
        'first_open' => true,
        'show_toggle_all' => true,
        'min' => 1,
        'max' => 20,
        'confirm_delete' => true,
        'confirm_delete_msg' => 'Zeile wirklich loeschen?',
    ])
;

echo $mform->show();
