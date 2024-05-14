<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Media file elements', MForm::factory()
        // some media fields
        ->addMediaField(1, array('label'=>'Image'))
        ->addMedialistField(1, array('label'=>'Image list'))
        ->addImagelistField(2, ['label' => 'Image List'])
    )
    // add second fieldset area
    ->addFieldsetArea('Link elements', MForm::factory()
        // some link elements
        ->addLinkField(1,array('label'=>'Link'))
        ->addLinklistField(1,array('label'=>'Link list'))
        ->addCustomLinkField(1, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
    );
// parse form
echo $mform->show();