<?php
// init mform
$mform = MForm::factory()
    // fieldset
    ->addFieldsetArea('Collapse elements', MForm::factory()
        ->addCollapseElement('Collapse 1', MForm::factory()
            ->addTextAreaField('5.0.1', ['label' => 'Text-Area 1'])
        , true) // open this collapse initial
        ->addCollapseElement('Collapse 2', MForm::factory()
            ->addTextAreaField('5.0.2', ['label' => 'Text-Area 2'])
        )
        ->addCollapseElement('Collapse 3', MForm::factory()
            ->addTextAreaField('5.0.3', ['label' => 'Text-Area 3'])
        )
    );
// parse mform
echo $mform->show();