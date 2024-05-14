<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // fieldset with placeholder
    ->addFieldsetArea('Placeholder', MForm::factory()
        // text input with placeholder
        ->addTextField("1.0", ['label'=>'Text input'], 'defaultValue')
        // textinput with placeholder
        ->addTextField(1.1, ['label'=>'Text input'])
            ->setDefaultValue('defaultValue')
    );
// parse form
echo $mform->show();