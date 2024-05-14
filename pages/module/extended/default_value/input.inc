<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // fieldset with placeholder
    ->addFieldsetArea('Placeholder', MForm::factory()
        // text
        ->addTextField("1.0", array('label'=>'Text input', 'placeholder' => 'Test Placeholder'))
        ->addTextAreaField(1.1, array('label'=>'Text input'))
            ->setPlaceholder('Test Placeholder')
        // checkboxes
        ->addCheckboxField(2, [1 => 'test-1'], ['label' => 'Checkbox'], 1) // use 1 as default value
        ->addToggleCheckboxField(3, [1 => 'Toggle test-1'], ['label' => 'Toggle Checkbox'], 1) // use 1 as default value
        // radio elements
        ->addRadioField(4, [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio Buttons'], 2) // use 2 as default value
    );
// parse form
echo $mform->show();