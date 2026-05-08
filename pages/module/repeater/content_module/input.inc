<?php
use FriendsOfRedaxo\MForm;

// Modul: Content-Pflege-Beispiel mit MFormOutput-Helpern
//   - Headline + Intro (rex_value 1, 2)
//   - Repeater "Sektionen" (rex_value 3) mit:
//       title, text (TinyMCE), image, link, gallery (Medialist)

$mform = MForm::factory()
    ->addTextField(1, ['label' => 'Headline', 'class' => 'form-control'])
    ->addTextAreaField(2, ['label' => 'Intro', 'rows' => 3])
    ->addRepeaterElement(3, MForm::factory()
        ->addTextField('title', ['label' => 'Titel'])
        ->addTextAreaField('text', ['label' => 'Text', 'class' => 'mform-tinymce'])
        ->addMediaField('image', ['label' => 'Hauptbild'])
        ->addCustomLinkField('link', ['label' => 'Link'])
        ->addMedialistField('gallery', ['label' => 'Galerie']),
        true,
        true,
        ['default_count' => 1, 'collapsed' => true, 'first_open' => true]
    );

echo $mform->show();
