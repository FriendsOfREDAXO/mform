<?php
// init mform
$mform = MForm::factory()
    // text input use set attribute method
    ->addTextField("1.0")
    ->setAttribute('label', 'Text Label')
    ->setAttribute('class', 'mynewclass')
    ->setAttribute('style', 'width: 260px')
    ->setAttribute('default-value', 'default value string');

// text input use set attributes method
$mform->addTextField(1.2)
    ->setAttributes([
        'label'=>'Text Label',
        'class'=>'mynewclass',
        'style'=>'width: 220px',
        'default-value'=>'default value string'
    ]);

// text input use add method attributes parameter
$mform->addTextField(1.3, [
    'label'=>'Text Label',
    'class'=>'mynewclass',
    'style'=>'width: 280px',
    'default-value'=>'default value string'
]);

// text input use any set methods
$mform->addTextField(1.4)
    ->setLabel('Text Label') // for label use set label method
    ->setAttribute('class', 'mynewclass') // for class use only set attribute method
    ->setAttribute('style', 'width: 220px') // for style use only set attribute method
    ->setDefaultValue('default value string'); // for default value use set default value method

// init mform
$mform2 = MForm::factory()
    // select use add method attributes parameter
    ->addSelectField("2.0", [1 => 'option 1', 2 => 'option 2'], [
        'label'=>'Select Label',
        'class'=>'mynewclass',
        'style'=>'width: 260px',
        'default-value'=>2
    ]);

// select use set attributes method
$mform->addSelectField(2.1, [1 => 'option 1', 2 => 'option 2'])
    ->setAttributes([
        'label'=>'Select Label',
        'class'=>'mynewclass',
        'style'=>'width: 220px',
        'default-value'=>2
    ]);

// select use any setters
$mform->addSelectField(2.2)
    ->setOptions([1 => 'option 1', 2 => 'option 2', 3 => 'option 3', 4 => 'option 4']) // for options set options method
    ->setOption('option 5', 5)
    ->setLabel('Select Label') // for label use set label method
    ->setAttribute('class', 'mynewclass') // for class use only set attribute method
    ->setAttribute('style', 'width: 260px') // for style use only set attribute method
    ->setDefaultValue(2) // for default value use set default value method
    ->setMultiple()
    ->setSize('full');

// parse mform
echo MForm::factory()
    // add fieldset areas
    ->addFieldsetArea('Fieldset Element', $mform)
    ->addFieldsetArea('Select elements with attributes', $mform2)
    ->show();