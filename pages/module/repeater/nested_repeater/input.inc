<?php
// init mform
$mform = MForm::factory()
    // text input element
    ->addTextField(1, ['label' => 'test rex value 1'])

    // repeater wrapper element level 1
    ->addRepeaterElement(2, MForm::factory()
        // set fieldset
        ->addFieldsetArea('fieldset1', MForm::factory()
            // text input element
            ->addTextField('text1', ['label' => 'Text 1'])
            // add second fieldset area
            ->addFieldsetArea('fieldset2', MForm::factory()
                // radio element
                ->addRadioField('radio1', [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio 1'])
            )

            // repeater wrapper element level 2
            ->addRepeaterElement('fields1', MForm::factory()
                // set fieldset
                ->addFieldsetArea('fieldset1', MForm::factory()
                    // text input element
                    ->addTextField('text2', ['label' => 'Text 2'])
                    // add second fieldset area
                    ->addFieldsetArea('fieldset2', MForm::factory()
                        // radio element
                        ->addRadioField('radio2', [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio 2'])
                    )
                )
            )
        )
    );

echo $mform->show();
