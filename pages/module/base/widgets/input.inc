<?php
use FriendsOfRedaxo\MForm;

// define your ylink table target
$ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']];

// init mform
echo MForm::factory()
    // add fieldset area
    ->addFieldsetArea('MForm Widgets', MForm::factory()
        // custom elements
        ->addCustomLinkField(1, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable', 'ylink' => $ylink])
        ->addImagelistField(1, ['label' => 'Image List']) // unfortunately no repeater support for imagelistFields
    )
    // parse form
    ->show();