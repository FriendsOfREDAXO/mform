<?php
use FriendsOfRedaxo\MForm;

$ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']];

// init mform
echo MForm::factory()
    // add fieldset area
    ->addFieldsetArea('MForm Widgets', MForm::factory()
        // custom elements
        ->addCustomLinkField(1, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable','ylink' => $ylink])
        ->addImagelistField(1, ['label' => 'Image List'])
    )
    // parse form
    ->show();




https://rex.uikit/redaxo/index.php?page=yform/manager/data_edit&
//
//table_name=rex_ycountries&rex_yform_manager_opener[id]=1&rex_yform_manager_opener[field]=de_de&rex_yform_manager_opener[multiple]=0