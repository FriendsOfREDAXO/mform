<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // toggle radio
    ->addFieldsetArea('Radio collapse element', MForm::factory()
        ->addRadioField('1.1', [1 => 'Open Collapse 1', 2 => 'Open Collapse 2' ,3 => 'Something...'], ['label' => 'Radio Buttons'])
            ->setToggleOptions([1 => 'collapse1', 2 => 'collapse2']) // user value "collapse1" and "collapse2" for collapse data-group-collapse-id
        ->addForm( // use addForm method to add the collapse element wrapper form
            MForm::factory()
                ->addCollapseElement('',
                    MForm::factory()->addTextAreaField('1.2', ['label' => 'Text-Area 1']),
                    false, true, ['data-group-collapse-id' => 'collapse1'] // open that collapse initial and hide the toggle link
                )
                ->addCollapseElement('',
                    MForm::factory()->addTextAreaField('1.3', ['label' => 'Text-Area 2']),
                    true, true, ['data-group-collapse-id' => 'collapse2'] // hide the toggle link
                )
        )
    )
    // toggle checkbox
    ->addFieldsetArea('Checkbox collapse element', MForm::factory()
        ->addCheckboxField('2.1', [1 => 'Open Collapse-area'], ['label' => 'Collapse checkbox', 'data-toggle-item' => 'collapse1'])
        ->addForm(MForm::factory()
            ->addCollapseElement('', MForm::factory()
                ->addTextField('2.2', ['label' => 'Button-Text (optional)'])
                ->addCustomLinkField('2.3', ['label' => 'Link-Ziel'])->show(), false, true, ['data-group-collapse-id' => 'collapse3']
            )
        )
    )
    // checkbox
    ->addFieldsetArea('Toggle checkbox collapse element', MForm::factory()
        ->addToggleCheckboxField('2.4', [1 => 'Open Collapse-area'], ['label' => 'Collapse checkbox', 'data-toggle-item' => 'collapse3'])
        ->addForm(MForm::factory()
            ->addCollapseElement('', MForm::factory()
                ->addTextField('2.5', ['label' => 'Button-Text (optional)'])
                ->addCustomLinkField('2.6', ['label' => 'Link-Ziel'])->show(), false, true, ['data-group-collapse-id' => 'collapse3']
            )
        )
    );

// parse form
echo $mform->show();