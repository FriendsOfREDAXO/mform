<?php

use FriendsOfRedaxo\MForm;

$tabItemForm = MForm::factory()
    ->addTabElement('Inhalt', MForm::factory()
        ->addTextField('title', ['label' => 'Block-Titel'])
        ->addTextAreaField('text', ['label' => 'Block-Text'])
    , true, false, [
        'tab-icon' => 'fa-align-left',
        'tab-style' => 'modern',
    ])
    ->addTabElement('Link', MForm::factory()
        ->addCustomLinkField('link', [
            'label' => 'Block-Link',
            'data-intern' => 'enable',
            'data-extern' => 'enable',
            'data-media' => 'enable',
            'data-mailto' => 'enable',
            'data-tel' => 'enable',
        ])
    , false, false, [
        'tab-icon' => 'fa-link',
        'tab-style' => 'modern',
    ]);

$sectionForm = MForm::factory()
    ->addTextField('section_label', ['label' => 'Sektionstitel'])
    ->addTabElement('Sektion Inhalt', MForm::factory()
        ->addRepeaterElement('blocks', $tabItemForm, true, true, [
            'label' => 'Repeater im Tab (mit Tabs im Item)',
            'btn_text' => 'Block hinzufügen',
            'copy_paste' => true,
            'collapsed' => false,
        ])
    , true, false, [
        'tab-icon' => 'fa-folder-open',
        'tab-style' => 'modern',
        'tab-layout' => 'vertical',
    ])
    ->addTabElement('Meta', MForm::factory()
        ->addSelectField('style', [
            'default' => 'Default',
            'highlight' => 'Highlight',
            'muted' => 'Muted',
        ], ['label' => 'Sektion-Stil'])
    , false, false, [
        'tab-icon' => 'fa-cog',
        'tab-style' => 'modern',
        'tab-layout' => 'vertical',
    ]);

$mform = MForm::factory()
    ->addTabElement('Modul-Einstellungen', MForm::factory()
        ->addTextField(1, ['label' => 'Modul-Headline'])
    , true, false, [
        'tab-icon' => 'fa-sliders',
        'tab-style' => 'modern',
    ])
    ->addTabElement('Sektionen', MForm::factory()
        ->addRepeaterElement(2, $sectionForm, true, true, [
            'label' => 'Sektionen',
            'btn_text' => 'Sektion hinzufügen',
            'copy_paste' => true,
            'collapsed' => false,
        ])
    , false, false, [
        'tab-icon' => 'fa-list',
        'tab-style' => 'modern',
    ]);

echo $mform->show();
