<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    ->addInlineElement('Label', MForm::factory()
        ->addTextField(1.1, ['label' => 'Test 3', 'full' => true])
        ->addTextField(1.2, ['label' => 'Test 4', 'full' => true])
    )
    ->addInlineElement('Label 2', MForm::factory()
        ->addTextField(2.1, ['label' => 'Test 5', 'full' => true])
        ->addTextField(2.2, ['label' => 'Test 6', 'full' => true])
    );
// parse mform
echo $mform->show();