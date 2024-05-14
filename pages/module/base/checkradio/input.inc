<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Checkbox element', MForm::factory()
        // some checkbox elements
        ->addCheckboxField(1, [1 => 'test-1'], ['label' => 'Checkbox'])
        ->addToggleCheckboxField(3, [1 => 'Toggle test-1'], ['label' => 'Toggle Checkbox'])
    )
    // add second fieldset area
    ->addFieldsetArea('Radio buttons element', MForm::factory()
        // radio element
        ->addRadioField(2, [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio Buttons'])
    );

// image options array for radioImgField
$options = [];
for ($i = 1; $i <= 20; $i++) { // for 1-20
    $options[$i] = ['img' => "/assets/backend/img/l$i.svg", 'label' => "Variante $i"]; // add 1-20 svg img from path /assets/backend/img
}

// add radio img fieldset area
$mform->addFieldsetArea('Radio Image', MForm::factory()
    // radio img element
    ->addRadioImgField(4, $options, ['label' => 'Layout Type']) // add options array to register images for radio choices
    ->addOptionField("<img src=\"/assets/backend/img/l21.svg\"><span>Variante 21<span>")
);
// add radio color fieldset area
$mform->addFieldsetArea('Radio Color', MForm::factory()
    // radio color element
    ->addRadioColorField('bg_color', [
        'transparent' => ['color' => "transparent", 'label' => "transparent"],
        'white' => ['color' => "#fff", 'label' => "white"],
        'black' => ['color' => "#000", 'label' => "black"],
        'red' => ['color' => "#ff0000", 'label' => "red"],
    ], ['label' => 'Hintergrund'])
);

// parse form
echo $mform->show();