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
    ->addCheckboxGroupField('tags', [
        'news' => 'News',
        'blog' => 'Blog',
        'event' => 'Events',
        'tutorial' => 'Tutorial',
    ], ['label' => 'Tags horizontal (Standard)'])
    ->addCheckboxGroupField('types', [
        'article' => 'Artikel',
        'page' => 'Seite',
        'media' => 'Medium',
    ], ['label' => 'Typen vertikal', 'layout' => 'vertical'])
    ->addCheckboxGroupField('priority', [
        'low' => 'Niedrig',
        'medium' => 'Mittel',
        'high' => 'Hoch',
    ], ['label' => 'Priorität (Radio-Mode)', 'mode' => 'radio'])
    ->addColorSwatchField('color', [
        '#ffffff' => 'Weiß',
        '#111111' => 'Schwarz',
        '#2f77bc' => 'Blau',
        '#e74c3c' => 'Rot',
        '#27ae60' => 'Grün',
        '#f39c12' => 'Orange',
        '.bg-primary' => ['label' => 'Primär (CSS)', 'preview' => '#2f77bc'],
        '.bg-secondary' => ['label' => 'Sekundär (CSS)', 'preview' => '#6c757d'],
    ], ['label' => 'Farbe (ColorSwatch)'])
    ->addTextAreaField('text', ['label' => 'Text (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addTextAreaField('markdown', ['label' => 'Markdown (MarkdownEditor)', 'class' => 'markdowneditor-editor', 'data-markdowneditor-profile' => 'default'])
    ->addLinklistField('cta_links', ['category' => 1], null, ['label' => 'CTA Links (Linklist)'])
    ->addMedialistField('downloads', ['types' => 'pdf,zip,jpg,png'], null, ['label' => 'Downloads (Medialist)'])
    ->addModalElement('Zeilen-Einstellungen', MForm::factory()
        ->addSelectField('row_bg', [
            '' => '– Standard –',
            'bg-light' => 'Hellgrau',
            'bg-dark text-white' => 'Dunkel',
            'bg-primary text-white' => 'Primärfarbe',
        ], ['label' => 'Hintergrundfarbe'])
        ->addSelectField('row_padding', [
            '' => '– Standard –',
            'py-2' => 'Klein',
            'py-4' => 'Mittel',
            'py-6' => 'Groß',
        ], ['label' => 'Abstand'])
        ->addCheckboxField('row_fullwidth', [1 => 'Volle Breite (fullwidth)'], ['label' => 'Layout'])
        ->addRepeaterElement('row_badges', MForm::factory()
            ->addTextField('badge_label', ['label' => 'Text'])
            ->addSelectField('badge_color', [
                'primary' => 'Blau',
                'success' => 'Grün',
                'danger'  => 'Rot',
                'warning' => 'Orange',
            ], ['label' => 'Farbe'])
        , true, false, ['label' => 'Badges (Repeater im Modal)', 'btn_text' => 'Badge hinzufügen', 'min' => 0, 'max' => 5])
        ->addAlertInfo('Diese Einstellungen betreffen nur diese Zeile und werden beim Speichern des Moduls übernommen.')
    , 'btn-default', 'center')
;

$mform = MForm::factory()
    ->addTabElement('Grunddaten', MForm::factory()
        ->addTextField(1, ['label' => 'Modul-Headline'])
        ->addSelectField(2, ['de' => 'Deutsch', 'en' => 'English'], ['label' => 'Sprache'])
        ->addToggleCheckboxField(3, [1 => 'Feature aktivieren'], ['label' => 'Feature'])
    , true)
    ->addTabElement('Darstellung', MForm::factory()
        ->addRadioImgField(4, $layoutOptions, ['label' => 'Layout'], 'cards')
        ->addRadioColorField(5, [
            'transparent' => ['color' => 'transparent', 'label' => 'Transparent'],
            'white' => ['color' => '#ffffff', 'label' => 'Weiss'],
            'black' => ['color' => '#111111', 'label' => 'Dunkel'],
            'blue' => ['color' => '#2f77bc', 'label' => 'Blau'],
        ], ['label' => 'Hintergrundfarbe'], 'transparent')
        ->addColorSwatchField(15, [
            '#ffffff' => 'Weiß',
            '#111111' => 'Schwarz',
            '#2f77bc' => 'Blau',
            '#e74c3c' => 'Rot',
            '#27ae60' => 'Grün',
            '.text-primary' => ['label' => 'Primär CSS', 'preview' => '#2f77bc'],
            '.text-muted' => ['label' => 'Gedämpft CSS', 'preview' => '#6c757d'],
        ], ['label' => 'Textfarbe (ColorSwatch)'])
        ->addCheckboxGroupField(14, [
            'news' => 'News',
            'blog' => 'Blog',
            'event' => 'Events',
            'tutorial' => 'Tutorial',
            'video' => 'Video',
        ], ['label' => 'Kategorien (CheckboxGroup)'], 'news,blog')
    )
    ->addTabElement('Widgets', MForm::factory()
        ->addCustomLinkField(6, ['label' => 'Hauptlink', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
        ->addImagelistField(7, ['label' => 'Bildliste'])
        ->addLinklistField(8, ['category' => 1], null, ['label' => 'Verwandte Artikel'])
        ->addMedialistField(9, ['types' => 'pdf,zip,jpg,png'], null, ['label' => 'Dateiliste'])
        ->addCustomLinkMultipleField(13, [
            'label' => 'Weitere Links (Multiple)',
            'btn_add' => 'Link hinzufügen',
            'data-intern' => 'enable',
            'data-extern' => 'enable',
            'data-media' => 'enable',
            'data-mailto' => 'enable',
            'data-tel' => 'enable',
        ])
    )
    ->addTabElement('Erweiterte Einstellungen', MForm::factory()
        ->addTextAreaField(10, ['label' => 'Hinweistext'])
        ->addSelectField(11, ['left' => 'Links', 'center' => 'Zentriert', 'right' => 'Rechts'], ['label' => 'Ausrichtung'])
    )
    ->addTabElement('Repeater', MForm::factory()
        ->addHtml(
            '<div class="alert alert-info">'
            . '<strong>Kopieren / Einfügen</strong><br>'
            . 'Jede Zeile kann über den <i class="rex-icon fa-copy"></i>-Button kopiert '
            . 'und mit <em>„Danach einfügen"</em> oder <em>„Paste (at end)"</em> wieder eingefügt werden. '
            . 'Das Clipboard bleibt auch nach einem Seitenreload erhalten (sessionStorage).<br><br>'
            . '<code>copy_paste =&gt; true</code> aktiviert die Funktion am Repeater-Element.'
            . '</div>'
        )
        ->addRepeaterElement(12, $rowForm, true, true, [
            'label' => 'Inhaltszeilen',
            'btn_text' => 'Zeile hinzufügen',
            'collapsed' => true,
            'first_open' => true,
            'show_toggle_all' => true,
            'min' => 1,
            'max' => 20,
            'confirm_delete' => true,
            'confirm_delete_msg' => 'Zeile wirklich löschen?',
            'copy_paste' => true,
        ])
    )
;

echo $mform->show();
