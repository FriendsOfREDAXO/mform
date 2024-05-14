<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    ->addInlineElement('Label', MForm::factory()
        ->addTextField(3, ['label' => 'Test 3', 'full' => true])
        ->addTextField(4, ['label' => 'Test 4', 'full' => true])
    )
    ->addInlineElement('Label 2', MForm::factory()
        ->addTextField(5, ['label' => 'Test 5', 'full' => true])
        ->addTextField(6, ['label' => 'Test 6', 'full' => true])
    );
// parse mform
echo $mform->show();