<?php
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

//$options = [];
//for ($i = 1; $i <= 20; $i++) {
//    $options[$i] = ['img' => "../assets/backend/img/l$i.svg", 'label' => "Variante $i"];
//}
//
//$mform->addFieldsetArea('', MForm::factory()
//    ->addRadioImgInlineField(4, $options, ['label' => 'Layout Type'])
//);

// parse form
echo $mform->show();