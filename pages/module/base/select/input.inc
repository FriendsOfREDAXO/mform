<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Select elements', MForm::factory()
        // some select fields
        ->addSelectField("1.0", [1 => 'test-1', 2 => 'test-2', 3 => 'test-3', 4 => 'test-4'], ['label' => 'Select optgroup'])
        ->addSelectField("1.1", ['group 1' => [1 => 'test-1', 2 => 'test-2'], 'group 2' => [3 => 'test-3', 4 => 'test-4']], ['label' => 'Select optgroup'])
    )
    // add second fieldset area
    ->addFieldsetArea('Multiselect elements', MForm::factory()
        // some multiple selects fields
        ->addMultiSelectField("2.0", [1 => 'test-1', 2 => 'test-2', 3 => 'test-3', 4 => 'test-4'], ['label' => 'Select optgroup'])
        ->addMultiSelectField("2.1", ['group 1' => [1 => 'test-1', 2 => 'test-2'], 'group 2' => [3 => 'test-3', 4 => 'test-4']], ['label' => 'Select optgroup'])
    );
// parse form
echo $mform->show();