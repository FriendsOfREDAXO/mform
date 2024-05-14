<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Select elements', MForm::factory()
        // some select fields
        // use string as id
        ->addSelectField("1.0", [1 => 'test-1', 2 => 'test-2', 3 => 'test-3', 4 => 'test-4'], ['label' => 'Select optgroup'])
        // use point seperated number as id
        ->addSelectField(1.1, ['group 1' => [1 => 'test-1', 2 => 'test-2'], 'group 2' => [3 => 'test-3', 4 => 'test-4']], ['label' => 'Select optgroup'])
    )
    // add second fieldset area
    ->addFieldsetArea('Multiselect elements', MForm::factory()
        // some multiple selects fields
        // use string as id
        ->addMultiSelectField("2.multiselect", [1 => 'test-1', 2 => 'test-2', 3 => 'test-3', 4 => 'test-4'], ['label' => 'Select optgroup'])
        ->addMultiSelectField("2.other_stuff", ['group 1' => [1 => 'test-1', 2 => 'test-2'], 'group 2' => [3 => 'test-3', 4 => 'test-4']], ['label' => 'Select optgroup'])
    );
// parse form
echo $mform->show();