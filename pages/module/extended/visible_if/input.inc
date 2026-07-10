<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()

    // Einfache Feld-Sichtbarkeit via setVisibleIf()
    ->addFieldsetArea('visible_if (Einzelfelder)', MForm::factory()
        ->addSelectField(1, [
            'text' => 'Text',
            'image' => 'Bild',
            'advanced' => 'Erweitert',
        ], ['label' => 'Select'])

        ->addTextField(2, ['label' => 'Text-Headline'])
            ->setVisibleIf(1, '=', 'text')

        ->addMediaField(3, null, null, ['label' => 'Hauptbild'])
            ->setVisibleIf(1, '=', 'image')
    )

    // Bereichs-Sichtbarkeit via addConditionalFieldsetArea()
    ->addConditionalFieldsetArea(1, '=', 'advanced', 'Conditional Fieldset', MForm::factory()
        ->addTextAreaField('textarea', ['label' => 'Textarea'])
    , ['class' => 'mform-conditional-wrapper'])

    // Repeater: visible_if funktioniert auch mit Feld-Keys
    ->addFlexRepeaterElement(4, MForm::factory()
        ->addToggleCheckboxField('toggle_checkbox', [1 => 'Zusatzfeld anzeigen'], ['label' => 'Toggle Checkbox'])
        ->addTextField('text', ['label' => 'Text'])
        ->addTextField('text_optional', ['label' => 'Text (visible_if)'])
            ->setVisibleIf('toggle_checkbox', '=', '1')
    , ['label' => 'Flex Repeater', 'first_open' => true, 'show_toggle_all' => false, 'open' => false, 'copy_paste' => false]);

echo $mform->show();
