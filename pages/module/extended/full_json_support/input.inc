<?php
use FriendsOfRedaxo\MForm;
// initialize
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Full json value support', MForm::factory()
        ->addTextField('1.0.title', ['label' => 'Text title'])
        ->addTextAreaField("1.0.description", ['label' => 'Text description'])
        ->addMultiSelectField("1.0.style", [1 => 'test-1', 2 => 'test-2'], ['label' => 'Multiselect style'])
    )
    // add second fieldset area
    ->addFieldsetArea('Numeric json values', MForm::factory()
        ->addTextField("1.1.0", ['label' => 'Text input'])
        ->addTextAreaField("1.1.1", ['label' => 'Textarea'])
        ->addMultiSelectField("1.1.2", [1 => 'test-1', 2 => 'test-2'], ['label' => 'Multiselect'])
    );
// parse form
echo $mform->show();