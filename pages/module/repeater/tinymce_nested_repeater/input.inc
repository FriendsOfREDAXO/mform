<?php
use FriendsOfRedaxo\MForm;

// Level-2-Formular: Schritte mit TinyMCE-Inhalt
$stepForm = MForm::factory()
    ->addTextField('title', ['label' => 'Schritt-Titel'])
    ->addTextAreaField('body', ['label' => 'Inhalt (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
;

// Level-1-Formular: Abschnitte mit TinyMCE-Einleitung + verschachteltem Repeater
$sectionForm = MForm::factory()
    ->addTextField('section_title', ['label' => 'Abschnitts-Titel'])
    ->addTextAreaField('intro', ['label' => 'Einleitung (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addRepeaterElement('steps', $stepForm, true, true, [
        'label'              => 'Schritte',
        'btn_text'           => 'Schritt hinzufügen',
        'confirm_delete'     => true,
        'confirm_delete_msg' => 'Schritt wirklich entfernen?',
        'collapsed'          => true,
        'first_open'         => true,
        'show_toggle_all'    => true,
        'min'                => 1,
        'max'                => 10,
    ])
;

$mform = MForm::factory()
    ->addTextField(1, ['label' => 'Seitentitel'])
    ->addRepeaterElement(2, $sectionForm, true, true, [
        'label'              => 'Abschnitte',
        'btn_text'           => 'Abschnitt hinzufügen',
        'confirm_delete'     => true,
        'confirm_delete_msg' => 'Abschnitt wirklich entfernen?',
        'collapsed'          => true,
        'first_open'         => true,
        'show_toggle_all'    => true,
    ])
;

echo $mform->show();
