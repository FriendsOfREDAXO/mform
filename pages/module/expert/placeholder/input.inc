<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // fieldset with placeholder
    ->addFieldsetArea('Placeholder', MForm::factory()
        // text input with placeholder
        ->addTextField("1.0", array('label'=>'Text input', 'placeholder' => 'Test Placeholder'))
        // textinput with placeholder
        ->addTextField(1.1, array('label'=>'Text input'))
            ->setPlaceholder('Test Placeholder')
    );
// parse form
echo $mform->show();