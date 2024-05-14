<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    ->addColumnElement(6, // column
        MForm::factory()->addTextField(1, ['label' => 'Test 1', 'full' => true])
    )
    ->addColumnElement(6, //column
        MForm::factory()->addTextField(2, ['label' => 'Test 2', 'full' => true]),
        ['class' => 'pl-0', 'data-test' => 'test123']
    );
// parse mform
echo $mform->show();