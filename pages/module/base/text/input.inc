<?php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Input Text elements', MForm::factory() // init new mform
        // add some text fields
        ->addTextField(1.0, ['label' => 'Input Text'])
        ->addTextField(1.2, ['label' => 'With options and Full width', 'full' => true])
            ->setOptions([1=>'option1',2=>'option2'])
    )
    // add second fieldset area
    ->addFieldsetArea('Textarea elements', MForm::factory() // init new mform
        ->addTextAreaField(1.3, ['label' => 'Textarea'])
        ->addTextAreaField(1.4, ['label' => 'Full width'])
            ->setFull() // or ->addTextAreaField(1.2,['full' => true])
    )
    // add third fieldset area
    ->addFieldsetArea('Readonly text elements', MForm::factory() // use mform factory
        // add some readonly text fields
        ->addTextReadOnlyField("2.0", 'string readonly', ['label' => 'Readonly Input Text'])
        ->addTextAreaReadOnlyField(2.1, 'string readonly', ['label' => 'Readonly Textarea'])
    );
// parse form
echo $mform->show();