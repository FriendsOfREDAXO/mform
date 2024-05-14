<?php

use FriendsOfRedaxo\MForm;

// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Checkbox element', MForm::factory()
        // some checkbox elements
        ->addCheckboxField(1, [1 => 'test-1'], ['label' => 'Checkbox'])
        ->addToggleCheckboxField(2, [1 => 'Toggle test-1'], ['label' => 'Toggle Checkbox'])
    )
    // add second fieldset area
    ->addFieldsetArea('Radio buttons element', MForm::factory()
        // radio element
        ->addRadioField(3, [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio Buttons'])
    );

// image options array for radioImgField
$options = [];
// add some images...
$options[0] = ['img' => "https://source.unsplash.com/random/300x300", 'label' => "Random"];

foreach (["https://images.unsplash.com/photo-1706818033048-a580b4c11bf8?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDE2OQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1709286636296-aab1c43038f4?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcxOTg4Mg&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1708114300068-254c8ae8bc43?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDA1OQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1708247899914-db888e0ce29a?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDAwOA&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1709065399368-6378eddc1562?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDA2OQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1708514193930-2977def8669a?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDA3NQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1706494350398-8e1da6a80f89?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDExMQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1709393361159-b915b32b061a?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDExNw&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1708200216325-845664d87f9a?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDEyNQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1707588883437-9b3709880e3b?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDEzMQ&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1708797215950-1574d12b3c9f?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDE0Mw&ixlib=rb-4.0.3&q=80&w=300",
             "https://images.unsplash.com/photo-1706112609669-0612963b4c54?crop=entropy&cs=tinysrgb&fit=crop&fm=jpg&h=300&ixid=MnwxfDB8MXxyYW5kb218MHx8fHx8fHx8MTcxNTcyMDE0OQ&ixlib=rb-4.0.3&q=80&w=300"
         ] as $index => $url) {
    $options[($index + 1)] = ['img' => $url, 'label' => "Img" . $index + 1];
}


// add radio img fieldset area
$mform->addFieldsetArea('Radio Image', MForm::factory()
    // radio img element
    // add options array to register images for radio choices
    ->addRadioImgField(4, $options, ['label' => 'Layout Type'], 2) // use 2 as default value
);
// add radio color fieldset area
$mform->addFieldsetArea('Radio Color', MForm::factory()
    // radio color element
    ->addRadioColorField(5, [
        'transparent' => ['color' => "transparent", 'label' => "transparent"],
        'white' => ['color' => "#fff", 'label' => "white"],
        'black' => ['color' => "#000", 'label' => "black"],
        'red' => ['color' => "#ff0000", 'label' => "red"],
    ], ['label' => 'Hintergrund'], 'transparent') // use transparent as default value
);

// parse form
echo $mform->show();