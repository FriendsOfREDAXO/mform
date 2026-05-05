<?php
/**
 * Demo: Conditional Fields (addConditionalFieldsetArea)
 *
 * Zeigt, wie Formularbereiche regelbasiert ein- und ausgeblendet werden.
 *
 * REX_VALUE[1] – Content-Typ (text | image | video)
 * REX_VALUE[2] – Toggle-Checkbox "Erweiterte Optionen"
 * REX_VALUE[3] – Text-Headline
 * REX_VALUE[4] – Text-Inhalt
 * REX_VALUE[5] – Hauptbild (Media)
 * REX_VALUE[6] – Alt-Text
 * REX_VALUE[7] – Video-URL
 * REX_VALUE[8] – Video-Autoplay-Checkbox
 * REX_VALUE[9] – CSS-Klasse (erweitert)
 * REX_VALUE[10] – Anker-ID (erweitert)
 */

use FriendsOfRedaxo\MForm;

$mform = MForm::factory()

    // ── Basis-Steuerung ──────────────────────────────────────────────────────
    ->addFieldsetArea('Inhaltstyp wählen', MForm::factory()
        ->addSelectField(1, [
            ''      => '– bitte wählen –',
            'text'  => 'Text',
            'image' => 'Bild',
            'video' => 'Video',
        ], ['label' => 'Content-Typ'])
        ->addToggleCheckboxField(2, [1 => 'Erweiterte Optionen aktivieren'], ['label' => 'Erweitert'])
    )

    // ── Bereich nur bei "text" ────────────────────────────────────────────────
    ->addConditionalFieldsetArea(1, '=', 'text', 'Text-Optionen', MForm::factory()
        ->addTextField(3, [
            'label'  => 'Headline',
            'notice' => 'Wird als <h2> ausgegeben.',
        ])
        ->addTextAreaField(4, ['label' => 'Inhalt'])
    )

    // ── Bereich nur bei "image" ───────────────────────────────────────────────
    ->addConditionalFieldsetArea(1, '=', 'image', 'Bild-Optionen', MForm::factory()
        ->addMediaField(5, null, null, ['label' => 'Hauptbild'])
        ->addTextField(6, [
            'label'  => 'Alt-Text',
            'notice' => 'Für Barrierefreiheit empfohlen.',
        ])
    )

    // ── Bereich nur bei "video" ───────────────────────────────────────────────
    ->addConditionalFieldsetArea(1, '=', 'video', 'Video-Optionen', MForm::factory()
        ->addTextField(7, [
            'label'       => 'Video-URL',
            'placeholder' => 'https://www.youtube.com/watch?v=...',
            'notice'      => 'YouTube- oder Vimeo-URLs werden automatisch eingebettet.',
        ])
        ->addCheckboxField(8, [1 => 'Autoplay aktivieren'], ['label' => 'Autoplay'])
    )

    // ── Erweiterter Bereich (via Checkbox) ────────────────────────────────────
    ->addConditionalFieldsetArea(2, '=', '1', 'Erweiterte Optionen', MForm::factory()
        ->addTextField(9, ['label' => 'CSS-Klasse'])
        ->addTextField(10, [
            'label'  => 'Anker-ID',
            'notice' => 'Wird als id-Attribut am Abschnitts-Container gesetzt.',
        ])
    )

    // ── Bereich ausblenden wenn "text" gewählt (action=hide Beispiel) ─────────
    ->addConditionalFieldsetArea(1, '=', 'text', 'Hinweis: Nur bei Bild und Video sichtbar', MForm::factory()
        ->addAlertInfo('Dieser Bereich ist bei Typ "Text" ausgeblendet. action = hide invertiert das Verhalten.')
    , [], false, false, 'hide')
;

echo $mform->show();
