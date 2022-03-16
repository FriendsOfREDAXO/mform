<?php
// init mform
echo MForm::factory()
    // add fieldset area
    ->addFieldsetArea('MForm Widgets', MForm::factory()
        // custom elements
        ->addCustomLinkField(1, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
        ->addImagelistField(1, ['label' => 'Image List'])
    )
    // parse form
    ->show();