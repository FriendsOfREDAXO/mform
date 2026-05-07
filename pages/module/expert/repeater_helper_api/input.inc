<?php

use FriendsOfRedaxo\MForm;

// Dieses Modul demonstriert alle MFormRepeaterHelper-Methoden:
// decode(), filterByField(), sortByField(), groupByField(), limitItems(), filterEnabledItems()

$statusOptions = [
    'published' => 'Veröffentlicht',
    'draft'     => 'Entwurf',
    'archived'  => 'Archiviert',
];

$categoryOptions = [
    'news'     => 'News',
    'blog'     => 'Blog',
    'tutorial' => 'Tutorial',
    'video'    => 'Video',
];

$rowForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addSelectField('status', $statusOptions, ['label' => 'Status'], 'published')
    ->addSelectField('category', $categoryOptions, ['label' => 'Kategorie'])
    ->addInputField('number', 'priority', ['label' => 'Priorität (1–10)', 'min' => '1', 'max' => '10'], '5')
    ->addTextField('author', ['label' => 'Autor'])
    ->addTextAreaField('teaser', ['label' => 'Teaser-Text'])
;

$mform = MForm::factory()
    ->addRepeaterElement(1, $rowForm, true, true, [
        'label'           => 'Beiträge',
        'btn_text'        => 'Beitrag hinzufügen',
        'collapsed'       => true,
        'first_open'      => true,
        'show_toggle_all' => true,
        'min'             => 1,
        'max'             => 20,
        'confirm_delete'  => true,
        'confirm_delete_msg' => 'Beitrag wirklich löschen?',
    ])
;

echo $mform->show();
