<?php

use FriendsOfRedaxo\MBlock\MBlock;
use FriendsOfRedaxo\MForm;

if (!class_exists(MBlock::class)) {
    echo rex_view::warning('Dieses Beispiel benötigt das AddOn mblock.');
    return;
}

$id = 1;

// Testfall fuer Rueckwaertskompatibilitaet:
// false = Core-Widgets fuer medialist/linklist
MForm::useCustomLinkForClassicWidgets(false);

$mform = MForm::factory()
    ->addTextField("$id.0.headline", ['label' => 'Headline'])
    ->addMedialistField("$id.0.medialist", ['label' => 'Medialist (Core bei false)'])
    ->addLinklistField("$id.0.linklist", ['label' => 'Linklist (Core bei false)'])
    ->addImagelistField("$id.0.imagelist", ['label' => 'Imagelist (MForm-Widget)']);

echo MBlock::show($id, $mform->show(), [
    'min' => 1,
    'max' => 5,
    'settings' => [
        'title' => true,
    ],
]);

// Optional fuer den Gegencheck:
// MForm::useCustomLinkForClassicWidgets(true);
