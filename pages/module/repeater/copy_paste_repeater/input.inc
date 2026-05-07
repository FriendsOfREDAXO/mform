<?php

use FriendsOfRedaxo\MForm;

// Flex Repeater mit Copy/Paste – direkte API: addFlexRepeaterElement()
// copy_paste => true: Item kopieren (Session) und als neues Item einfügen
// min / max: Mindest- und Höchstanzahl an Items
// default_count: Anzahl vorbefüllter Items beim ersten Speichern
// collapsed: Items standardmäßig zugeklappt
// first_open: Erstes Item beim Laden aufgeklappt
// show_toggle_all: Alle auf-/zuklappen Button anzeigen

$itemForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel', 'placeholder' => 'z. B. Produktname'])
    ->addTextAreaField('text', ['label' => 'Beschreibung', 'rows' => 3])
    ->addSelectField('category', [
        'news'    => 'News',
        'product' => 'Produkt',
        'event'   => 'Event',
        'page'    => 'Seite',
    ], ['label' => 'Kategorie'])
    ->addMediaField('image', ['label' => 'Bild'])
    ->addToggleCheckboxField('active', [1 => 'Item aktiv (wird im Frontend angezeigt)'], ['label' => 'Status'], '1');

echo MForm::factory()
    ->addTabElement('Flex Repeater (Copy/Paste)', MForm::factory()

        ->addFieldsetArea('Wiederholbare Inhalts-Items', MForm::factory()
            ->addFlexRepeaterElement(1, $itemForm, [
                'label'          => 'Inhalts-Items',
                'copy_paste'     => true,    // Kopierfunktion aktivieren (Clipboard-Icon im Item-Header)
                'min'            => 1,       // mind. 1 Item
                'max'            => 10,      // max. 10 Items
                'default_count'  => 2,       // 2 Items beim ersten Öffnen vorbefüllen
                'collapsed'      => true,    // Items zugeklappt anzeigen
                'first_open'     => true,    // erstes Item aufgeklappt
                'show_toggle_all'=> true,    // "Alle auf-/zuklappen" Button
                'btn_text'       => '+ Item hinzufügen',
            ])
        )

    )
    ->addTabElement('Einfacher Flex Repeater', MForm::factory()

        ->addFieldsetArea('Minimalbeispiel', MForm::factory()
            // addFlexRepeaterElement ohne Optionen = Standardverhalten
            ->addFlexRepeaterElement(2, MForm::factory()
                ->addTextField('name', ['label' => 'Name'])
                ->addCustomLinkField('link', ['label' => 'Link'])
            )
        )

    )
    ->show();
