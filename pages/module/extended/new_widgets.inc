<?php

use FriendsOfRedaxo\MForm;

// init mform
$mform = MForm::factory()
    // add fieldset area for datetime fields
    ->addFieldsetArea('DateTime Picker Elements', MForm::factory()
        ->addDateTimeField(1, ['label' => 'Event DateTime'])
        ->addDateField(2, ['label' => 'Birth Date'])
        ->addTimeField(3, ['label' => 'Meeting Time'])
        ->addDateTimeField(4, ['label' => 'Custom Format', 'data-date-format' => 'd.m.Y H:i'], 'd.m.Y H:i')
    );

// image options array for checkboxImgField
$checkboxOptions = [];
// add some images...
$checkboxOptions[1] = ['img' => "https://images.unsplash.com/photo-1706818033048-a580b4c11bf8?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=150&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDE2OQ&ixlib=rb-4.0.3&q=80&w=150", 'label' => "Option 1"];
$checkboxOptions[2] = ['img' => "https://images.unsplash.com/photo-1709286636296-aab1c43038f4?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=150&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcxOTg4Mg&ixlib=rb-4.0.3&q=80&w=150", 'label' => "Option 2"];
$checkboxOptions[3] = ['img' => "https://images.unsplash.com/photo-1708114300068-254c8ae8bc43?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=150&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDA1OQ&ixlib=rb-4.0.3&q=80&w=150", 'label' => "Option 3"];
$checkboxOptions[4] = ['img' => "https://images.unsplash.com/photo-1708247899914-db888e0ce29a?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=150&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDAwOA&ixlib=rb-4.0.3&q=80&w=150", 'label' => "Option 4"];

// add checkbox img fieldset area
$mform->addFieldsetArea('Checkbox with Images', MForm::factory()
    // checkbox img element
    // add options array to register images for checkbox choices  
    ->addCheckboxImgField(5, $checkboxOptions, ['label' => 'Select Multiple Options'])
);

// parse form
echo $mform->show();