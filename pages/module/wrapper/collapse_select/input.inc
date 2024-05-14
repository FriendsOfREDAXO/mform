<?php
use FriendsOfRedaxo\MForm;
// select collapse
echo MForm::factory()
    ->addFieldsetArea('Select collapse element',
        MForm::factory()
            ->addSelectField('1.select', [0 => 'Bitte wählen', 1 => 'collapse 1', 2 => 'collapse 2', 3 => 'collapse 3'], ['label' => 'Select collapse', 'data-toggle' => 'collapse'])
                ->setToggleOptions([1 => 'collapse1', 2 => 'collapse2', 3 => 'collapse3']) // user value "collapse1", "collapse2" and "collapse3" for collapse data-group-collapse-id
            ->addForm(
                MForm::factory()
                    ->addCollapseElement('',
                        MForm::factory()->addTextAreaField('2.1', ['label' => 'Text-Area 1']),
                        false, true, ['data-group-collapse-id' => 'collapse1']
                    )
                    ->addCollapseElement('',
                        MForm::factory()->addTextAreaField('2.2', ['label' => 'Text-Area 2']),
                        true, true, ['data-group-collapse-id' => 'collapse2']
                    )
                    ->addCollapseElement('',
                        MForm::factory()->addTextAreaField('2.3', ['label' => 'Text-Area 3']),
                        false, true, ['data-group-collapse-id' => 'collapse3']
                    )
            )
    )
    // parse mform
    ->show();
