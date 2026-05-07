<?php
use FriendsOfRedaxo\MForm;

// Haupt-Formular mit Modal für Erweiterte Einstellungen
$mform = MForm::factory()
    ->addTextField(1, ['label' => 'Überschrift', 'placeholder' => 'Titel eingeben …'])
    ->addTextAreaField(2, ['label' => 'Text'])

    // Einfaches Modal für Block-Einstellungen (Felder speichern mit dem Formular)
    ->addModalElement('Block-Einstellungen', MForm::factory()
        ->addSelectField(10, [
            '' => '– Standard –',
            'bg-light' => 'Hellgrau',
            'bg-dark text-white' => 'Dunkel',
            'bg-primary text-white' => 'Primärfarbe',
        ], ['label' => 'Hintergrundfarbe'])
        ->addSelectField(11, [
            '' => '– Standard –',
            'py-2' => 'Klein (py-2)',
            'py-4' => 'Mittel (py-4)',
            'py-6' => 'Groß (py-6)',
        ], ['label' => 'Vertikaler Abstand'])
        ->addCheckboxField(12, ['1' => 'Volle Breite (fullwidth)'], ['label' => 'Layout'])
    )

    // Modal mit Hilfetext (addHtml als reines Info-Modal)
    ->addModalElement('Hilfe & Tipps', MForm::factory()
        ->addAlertInfo('Überschrift: Wird als &lt;h2&gt; ausgespielt. Markdown wird unterstützt.')
        ->addAlertSuccess('Tipp: Die Block-Einstellungen beeinflussen nur den umgebenden Container.')
        ->addHtml('<p class="help-block">Diese Felder benötigen keine Eingabe – der Block funktioniert auch ohne Einstellungen.</p>')
    , 'btn-info')
;

echo $mform->show();
