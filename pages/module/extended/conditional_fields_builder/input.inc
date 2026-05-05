<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addFieldsetArea('Basis', MForm::factory()
        ->addSelectField(1, [
            'text' => 'Text',
            'image' => 'Bild',
            'video' => 'Video',
        ], ['label' => 'Content-Typ'])
        ->addToggleCheckboxField(2, [1 => 'Erweiterte Optionen aktivieren'], ['label' => 'Erweitert'])
    )
    ->addConditionalFieldsetArea(1, '=', 'text', 'Text-Optionen', MForm::factory()
        ->addTextField(3, ['label' => 'Text-Headline'])
        ->addTextAreaField(4, ['label' => 'Text-Inhalt'])
    )
    ->addConditionalFieldsetArea(1, '=', 'image', 'Bild-Optionen', MForm::factory()
        ->addMediaField(5, null, null, ['label' => 'Hauptbild'])
        ->addTextField(6, ['label' => 'Alt-Text'])
    )
    ->addConditionalFieldsetArea(2, '=', '1', 'Erweiterte Optionen', MForm::factory()
        ->addTextField(7, ['label' => 'CSS-Klasse'])
        ->addTextField(8, ['label' => 'Anker-ID'])
    )
;

echo $mform->show();
