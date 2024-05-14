<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // toggle radio
    ->addFieldsetArea('Radio collapse element', MForm::factory()
        ->addRadioField('4.0.1', [1 => 'Open Collapse 1', 2 => 'Open Collapse 2' ,3 => 'Something...'], ['label' => 'Radio Buttons'])
            ->setToggleOptions([1 => 'collapse1', 2 => 'collapse2']) // user value "collapse1" and "collapse2" for collapse data-group-collapse-id
        ->addForm( // use addForm method to add the collapse element wrapper form
            MForm::factory()
                ->addCollapseElement('',
                    MForm::factory()->addTextAreaField('4.0.2', ['label' => 'Text-Area 1']),
                    false, true, ['data-group-collapse-id' => 'collapse1'] // open that collapse initial and hide the toggle link
                )
                ->addCollapseElement('',
                    MForm::factory()->addTextAreaField('4.0.3', ['label' => 'Text-Area 2']),
                    true, true, ['data-group-collapse-id' => 'collapse2'] // hide the toggle link
                )
        )
    )
    // toggle checkbox
    ->addFieldsetArea('Checkbox collapse element', MForm::factory()
        ->addCheckboxField('5.0.1', [1 => 'Open Collapse-area'], ['label' => 'Collapse checkbox', 'data-toggle-item' => 'collapse1'])
        ->addForm(MForm::factory()
            ->addCollapseElement('', MForm::factory()
                ->addTextField('5.0.2', ['label' => 'Button-Text (optional)'])
                ->addCustomLinkField('5.0.3', ['label' => 'Link-Ziel'])->show(), false, true, ['data-group-collapse-id' => 'collapse3']
            )
        )
    )
    // checkbox
    ->addFieldsetArea('Toggle checkbox collapse element', MForm::factory()
        ->addToggleCheckboxField('6.0.4', [1 => 'Open Collapse-area'], ['label' => 'Collapse checkbox', 'data-toggle-item' => 'collapse3'])
        ->addForm(MForm::factory()
            ->addCollapseElement('', MForm::factory()
                ->addTextField('6.0.5', ['label' => 'Button-Text (optional)'])
                ->addCustomLinkField('6.0.6', ['label' => 'Link-Ziel'])->show(), false, true, ['data-group-collapse-id' => 'collapse3']
            )
        )
    );

// parse form
echo $mform->show();