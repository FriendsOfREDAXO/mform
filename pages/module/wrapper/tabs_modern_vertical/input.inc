<?php

use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTabElement('Inhalt', MForm::factory()
        ->addTextField(1, ['label' => 'Titel'])
        ->addTextAreaField(2, ['label' => 'Teaser'])
    , true, false, [
        'tab-icon' => 'fa-file-text-o',
        'tab-style' => 'modern',
        'tab-layout' => 'vertical',
    ])
    ->addTabElement('SEO', MForm::factory()
        ->addTextField(3, ['label' => 'Meta-Title'])
        ->addTextAreaField(4, ['label' => 'Meta-Description'])
    , false, false, [
        'tab-icon' => 'fa-search',
        'tab-style' => 'modern',
        'tab-layout' => 'vertical',
    ])
    ->addTabElement('Call to Action', MForm::factory()
        ->addCustomLinkField(5, [
            'label' => 'CTA-Link',
            'data-intern' => 'enable',
            'data-extern' => 'enable',
            'data-media' => 'enable',
            'data-mailto' => 'enable',
            'data-tel' => 'enable',
        ])
        ->addTextField(6, ['label' => 'Button-Text'])
    , false, false, [
        'tab-icon' => 'fa-bullhorn',
        'tab-style' => 'modern',
        'tab-layout' => 'vertical',
    ]);

echo $mform->show();
