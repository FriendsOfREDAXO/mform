<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Select elements with options', MForm::factory()
        // select use add method options parameter
        ->addSelectField("1.0", [1 => 'option 1', 2 => 'option 2'], ['label'=>'Select Label'])
        // select use set option method
        ->addSelectField(1.1)
            ->setOption(1, 'option 1')
            ->setOption(2, 'option 2')
            ->setLabel('Select Label')
        // select use set options method
        ->addSelectField(1.2)
            ->setOptions([1 => 'option 1', 2 => 'option 2', 3 => 'option 3', 4 => 'option 4']) // for options set options method
            ->setLabel('Select Label') // for label use set label method
    )
    // add second fieldset area
    ->addFieldsetArea('Multiselect elements with options', MForm::factory()
        // multiselect use add method options parameter
        ->addMultiSelectField("2.0", [1 => 'option 1', 2 => 'option 2'], ['label'=>'Multiselect Label'])
        ->addSelectField("3.0", ['optgroup 1' => [1 => 'option 1', 2 => 'option 2'], 'optgroup 2' => [3 => 'option 3', 4 => 'option 4']], ['label'=>'Multiselect Label'])
            ->setSize('full') // do it full
            ->setMultiple()
        // select use set options method
        ->addSelectField(3.1)
            ->setOptions(['optgroup 1' => [1 => 'option 1', 2 => 'option 2'], 'optgroup 2' => [3 => 'option 3', 4 => 'option 4']]) // for options set options method
            ->setLabel('Multiselect Label')
            ->setMultiple()
    )
    // add third fieldset area
    ->addFieldsetArea('Checkbox element with option', MForm::factory()
        // checkbox element with option
        ->addCheckboxField(4)
            ->setOption(1, 'option 1') // checkboxes can only have one option
            ->setLabel('Checkbox Label')
    )
    // add fourth fieldset area
    ->addFieldsetArea('Radio button element with options', MForm::factory()
        ->addRadioField(5, [1 => 'option 1', 2 => 'option 2'], ['label' => 'Radio Buttons'])
            ->setOptions([1 => 'test-1', 2 => 'test-2', 3 => 'test-3']) // overwrite options
            ->setLabel('Radio Label') // overwrite label
    )
    // add fifth fieldset area
    ->addFieldsetArea('SQL options', MForm::factory()
        ->addRadioField(6, [], ['label' => 'SQL Radio'])
            ->setSqlOptions("select id, name from rex_module limit 5")
        ->addSelectField(7, [], ['label' => 'SQL Select'])
            ->setSqlOptions("select id, name from rex_module")
    );
// parse form
echo $mform->show();