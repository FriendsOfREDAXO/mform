<?php

use FriendsOfRedaxo\MForm;

// Read-Only- und Hidden-Felder
// addTextReadOnlyField    – zeigt einen Wert an, nicht editierbar, wird aber gespeichert
// addTextAreaReadOnlyField – mehrzeiliges Read-Only-Feld
// addHiddenField          – unsichtbares Feld mit festem oder dynamischem Wert

// Dynamischer Wert, der als Hidden-Feld mit gespeichert wird (z. B. Erstellungsdatum)
$createdAt = date('Y-m-d H:i:s');

echo MForm::factory()
    ->addTabElement('Read-Only-Felder', MForm::factory()

        ->addFieldsetArea('addTextReadOnlyField', MForm::factory()
            ->addTextReadOnlyField(1, 'Dieser Text wird angezeigt, aber nicht verändert', [
                'label'  => 'Read-Only Text',
                'notice' => 'addTextReadOnlyField(id, value, attributes) – zeigt den Wert an und speichert ihn. Ideal für berechnete oder fixierte Werte.',
            ])
            ->addTextReadOnlyField(2, 'Versionsnummer: 9.0.0', [
                'label' => 'System-Info',
            ])
        )

        ->addFieldsetArea('addTextAreaReadOnlyField', MForm::factory()
            ->addTextAreaReadOnlyField(3, "Zeile 1: Erster Absatz\nZeile 2: Zweiter Absatz\nZeile 3: Dritter Absatz", [
                'label'  => 'Read-Only Textarea',
                'notice' => 'Mehrzeilige Read-Only-Anzeige – z. B. für generierte Texte oder Vorschauen.',
                'rows'   => 4,
            ])
        )

    )
    ->addTabElement('Hidden-Felder', MForm::factory()

        ->addFieldsetArea('addHiddenField', MForm::factory()
            // Hinweis-Text (kein Formular-Element)
            ->addHtml('<div class="alert alert-info">Hidden-Felder sind im Formular unsichtbar, werden aber zusammen mit dem Modul gespeichert.</div>')
            ->addTextField(4, [
                'label'  => 'Sichtbares Feld',
                'notice' => 'Dieses Feld ist normal editierbar.',
            ])
            // Fixer Wert (z. B. Modul-Typ-Kennung)
            ->addHiddenField(5, 'article_teaser', [
                'notice' => 'addHiddenField(id, value) – speichert "article_teaser" unsichtbar in REX_VALUE[id=5].',
            ])
            // Dynamisch generierter Wert
            ->addHiddenField(6, $createdAt)
            ->addHtml('<p class="help-block">Versteckter Zeitstempel in REX_VALUE[id=6]: <code>' . rex_escape($createdAt) . '</code> (wird beim Speichern mitgespeichert)</p>')
        )

        ->addFieldsetArea('Typischer Anwendungsfall: Modul-Typ kennzeichnen', MForm::factory()
            ->addHtml('<div class="alert alert-warning">Pattern: Ein Hidden-Feld mit dem Modul-Typ ermöglicht im Output-Template ein sauberes Switch-Statement über mehrere Module.</div>')
            ->addTextField(7, ['label' => 'Headline'])
            ->addTextAreaField(8, ['label' => 'Text'])
            ->addHiddenField(9, 'content-text')
        )

    )
    ->show();
