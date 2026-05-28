<?php

use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addSelectField(2, [
        'bootstrap5' => 'Bootstrap 5',
        'uikit3' => 'UIkit 3',
        'bulma' => 'Bulma',
    ], ['label' => 'Output-Framework'], 1, 'bootstrap5')
    ->addContentBlocksElement(1, [
        'label' => 'Content Blocks',
        'tiny_profile' => 'default',
        'btn_text' => 'Block hinzufuegen',
        'copy_paste' => true,
        'collapsed' => false,
    ])
    ->show();
