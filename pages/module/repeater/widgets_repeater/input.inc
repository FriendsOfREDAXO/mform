<?php
use FriendsOfRedaxo\MForm;

$yLink = [['name' => 'Countries', 'table'=>'rex_countries', 'column' => 'code']];
$mform = MForm::factory()
    // repeater wrapper element level 1
    ->addRepeaterElement(1, MForm::factory()
        // add custom link element
        ->addCustomLinkField('custom_link', ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable','ylink' => $yLink])

        // repeater wrapper element level 2
        ->addRepeaterElement('fields1', MForm::factory()
            // add custom link element
            ->addCustomLinkField('custom_link', ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable','ylink' => $yLink])
            , true, false, ['btn_text' => 'Repeater Level2 Hinzufügen', 'max' => 4, 'min' => 2]
        )
        , true, true, ['btn_text' => 'Repeater Level1 Hinzufügen', 'confirm_delete_msg' => 'Repeater Level1 löschen?']
    );

echo $mform->show();
