<?php

use FriendsOfRedaxo\MForm;

// addCustomLinkMultipleField – mehrere Links in einem Feld
// Speichert alle Links als JSON-Array in einem einzelnen REX_VALUE-Slot.
// Jeder Eintrag ist ein vollständiger Custom-Link-String (intern, extern, Medien, mailto etc.)

// addCustomLinkField – einzelner Custom-Link (zum Vergleich)

echo MForm::factory()
    ->addTabElement('Einzelner Link', MForm::factory()
        ->addFieldsetArea('addCustomLinkField', MForm::factory()
            ->addCustomLinkField(1, [
                'label'       => 'Einzelner Link (CTA-Button)',
                'notice'      => 'Speichert einen einzelnen Link-String. Intern, extern, Medien und mailto werden unterstützt.',
                'data-intern' => '1',
                'data-extern' => '1',
                'data-media'  => '0',
                'data-mailto' => '1',
                'data-tel'    => '1',
            ])
        )
    )
    ->addTabElement('Mehrere Links', MForm::factory()
        ->addFieldsetArea('addCustomLinkMultipleField – Navigation / Link-Liste', MForm::factory()
            ->addCustomLinkMultipleField(2, [
                'label'       => 'Link-Liste',
                'notice'      => 'Alle Links werden als JSON-Array in einem Value-Slot gespeichert. Reihenfolge per Drag & Drop veränderbar.',
                'btn_add'     => 'Link hinzufügen',
                'data-intern' => '1',
                'data-extern' => '1',
                'data-media'  => '1',
                'data-mailto' => '1',
            ])
        )
        ->addFieldsetArea('addCustomLinkMultipleField – Download-Links', MForm::factory()
            ->addCustomLinkMultipleField(3, [
                'label'       => 'Download-Links',
                'notice'      => 'Für reine Download-Links: nur Medien-Picker aktiviert.',
                'btn_add'     => 'Download hinzufügen',
                'data-intern' => '0',
                'data-extern' => '1',
                'data-media'  => '1',
                'data-mailto' => '0',
            ])
        )
    )
    ->show();
