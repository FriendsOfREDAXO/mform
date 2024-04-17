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
            ->addTextField('test1', ['label' => 'test rex value 1'])
            // add second fieldset area
            ->addFieldsetArea('fieldset2', MForm::factory()
                // radio element
                ->addRadioField('radio1', [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio1'])
            )

            // repeater wrapper element level 2
            ->addRepeaterElement('fields1', MForm::factory()
                // set fieldset
                ->addFieldsetArea('fieldset', MForm::factory()
                    // text input element
                    ->addTextField('test2', ['label' => 'test rex value 1'])
                    // add second fieldset area
                    ->addFieldsetArea('fieldset2', MForm::factory()
                        // radio element
                        ->addRadioField('radio2', [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio2'])
                    )
                )
            )
        )
    );

echo $mform->show();
