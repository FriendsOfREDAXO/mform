<?php
use FriendsOfRedaxo\MForm;
echo MForm::factory()
    ->addFieldsetArea('HTML5-Eingabefelder', MForm::factory()
        ->addNumberField('1.0.menge', ['label' => 'Menge', 'min' => 1, 'max' => 99, 'step' => 1], '1')
        ->addRangeField('1.0.opacity', ['label' => 'Deckkraft (%)', 'min' => 0, 'max' => 100, 'step' => 5], '80')
        ->addDateField('1.0.start', ['label' => 'Beginn'])
        ->addDateTimeField('1.0.termin', ['label' => 'Termin'])
        ->addTimeField('1.0.uhrzeit', ['label' => 'Uhrzeit', 'step' => 900])
        ->addEmailField('1.0.mail', ['label' => 'E-Mail', 'placeholder' => 'name@example.org'])
        ->addColorField('1.0.farbe', ['label' => 'Farbe'], '#2f77bc')
    )
    ->show();
