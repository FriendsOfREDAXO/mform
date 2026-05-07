# Tutorial: Vom Einsteigermodul zum Seitenpflege-Profi

Keine Sorge – du musst kein PHP-Experte sein, um diesem Tutorial zu folgen.
Es zeigt Schritt für Schritt, wie ein REDAXO-Modul mit MForm aufgebaut wird, und erklärt dabei, **was jede Zeile tut und warum**.

Jedes Modul in REDAXO besteht aus zwei Teilen:

| Datei | Was sie tut |
|-------|-------------|
| `input.inc` | Definiert die Eingabemaske im Backend – was der Redakteur sieht und befüllen kann |
| `output.inc` | Bestimmt, was auf der Website erscheint – also die Ausgabe im Frontend |

Die Eingabemaske speichert Werte unter Slot-Nummern (1, 2, 3 …). Im Output werden diese Slots über Platzhalter wie `REX_VALUE[id=1]` ausgelesen – REDAXO ersetzt diese automatisch mit den gespeicherten Werten.

---

## Schritt 1 – Der Einstieg: Bild und Text

Das Modul gibt dem Redakteur genau drei Felder: ein Bild, eine Überschrift und einen kurzen Text.
Das ist die Grundstruktur, auf der alles Folgende aufbaut.

### input.inc

```php
<?php
use FriendsOfRedaxo\MForm;

// MForm::factory() startet die Formulardefinition.
// Jede Methode hängt ein weiteres Feld an.
// Die Zahl (1, 2, 3) ist der Speicherslot – sie muss innerhalb des Moduls eindeutig sein.
echo MForm::factory()
    ->addMediaField(1, ['label' => 'Bild'])          // Slot 1: Bild aus dem Medienpool
    ->addTextField(2, ['label' => 'Überschrift'])     // Slot 2: einzeiliges Textfeld
    ->addTextAreaField(3, ['label' => 'Text'])        // Slot 3: mehrzeiliges Textfeld
    ->show(); // Formular ausgeben
```

### output.inc

```php
<?php
// REX_MEDIA[id=1] liefert den Dateinamen des gespeicherten Bildes (z. B. "meinbild.jpg").
// REX_VALUE[id=2] liefert den eingetippten Text aus Slot 2, usw.
$image    = 'REX_MEDIA[id=1]';
$headline = 'REX_VALUE[id=2]';
$text     = 'REX_VALUE[id=3]';

// Nur ausgeben, wenn auch wirklich etwas eingegeben wurde:
if ($image) {
    // rex_url::media() baut den richtigen Pfad zur Mediendatei zusammen.
    echo '<figure><img src="' . rex_url::media($image) . '" alt="' . rex_escape($headline) . '"></figure>';
}

if ($headline) {
    // rex_escape() schützt vor eingeschleustem HTML (immer verwenden bei Texteingaben!).
    echo '<h2>' . rex_escape($headline) . '</h2>';
}

if ($text) {
    // nl2br() wandelt Zeilenumbrüche in HTML-<br>-Tags um.
    echo '<p>' . nl2br(rex_escape($text)) . '</p>';
}
```

---

## Schritt 2 – Formatierter Text mit dem WYSIWYG-Editor

Manchmal reicht ein einfaches Textfeld nicht. Redakteure möchten **fett**, *kursiv*, Aufzählungen und Links.
Dafür schalten wir TinyMCE für das Textfeld ein – indem wir es schlicht als Editor-Klasse markieren.
Der Rest passiert automatisch.

### input.inc

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addMediaField(1, ['label' => 'Bild'])
    ->addTextField(2, ['label' => 'Überschrift'])
    ->addTextAreaField(3, [
        'label'        => 'Inhalt',
        // Diese beiden Attribute aktivieren TinyMCE für dieses Feld.
        // 'default' ist das Editor-Profil – kann im TinyMCE-Addon angepasst werden.
        'class'        => 'tiny-editor',
        'data-profile' => 'default',
    ])
    ->show();
```

### output.inc

```php
<?php
$image    = 'REX_MEDIA[id=1]';
$headline = 'REX_VALUE[id=2]';
$html     = 'REX_VALUE[id=3]'; // enthält jetzt fertiges HTML vom Editor

if ($image) {
    echo '<figure><img src="' . rex_url::media($image) . '" alt="' . rex_escape($headline) . '"></figure>';
}

if ($headline) {
    echo '<h2>' . rex_escape($headline) . '</h2>';
}

if ($html) {
    // Wichtig: HTML aus dem WYSIWYG-Editor NICHT escapen – es ist bereits sicheres HTML.
    // Deshalb kein rex_escape() hier, sondern direkte Ausgabe.
    echo '<div class="wysiwyg-content">' . $html . '</div>';
}
```

---

## Schritt 3 – Ein Button mit smartem Link

Fast jeder Inhaltsbaustein braucht irgendwann einen Call-to-Action-Button.
Der Custom-Link-Picker erlaubt dem Redakteur, **intern** (einen REDAXO-Artikel), **extern** (eine beliebige URL), eine **Mediendatei**, eine **E-Mail** oder eine **Telefonnummer** zu verlinken – alles mit demselben Feld.

### input.inc

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addMediaField(1, ['label' => 'Bild'])
    ->addTextField(2, ['label' => 'Überschrift'])
    ->addTextAreaField(3, ['label' => 'Inhalt', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addCustomLinkField(4, [
        'label'        => 'Button-Link',
        // Jede dieser Optionen aktiviert eine Verknüpfungsart im Picker-Dialog:
        'data-intern'  => 'enable',  // interne Artikel
        'data-extern'  => 'enable',  // externe URLs
        'data-media'   => 'enable',  // Dateien aus dem Medienpool
        'data-mailto'  => 'enable',  // E-Mail-Adressen
        'data-tel'     => 'enable',  // Telefonnummern
    ])
    ->show();
```

### output.inc

```php
<?php
// MFormOutputHelper kümmert sich darum, den gespeicherten Link-Wert
// in eine echte URL und einen Linktext umzuwandeln – egal ob intern, extern usw.
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$headline  = 'REX_VALUE[id=2]';
$html      = 'REX_VALUE[id=3]';
$linkValue = 'REX_VALUE[id=4]'; // der rohe Wert aus dem Custom-Link-Picker

if ($headline) {
    echo '<h2>' . rex_escape($headline) . '</h2>';
}
if ($html) {
    echo '<div class="wysiwyg-content">' . $html . '</div>';
}

// getCustomUrl() liefert die fertige URL (oder leeren String, wenn kein Link gesetzt).
$url      = MFormOutputHelper::getCustomUrl($linkValue);
// prepareCustomLink() liefert u. a. das target-Attribut (z. B. target="_blank" für externe Links).
$linkData = MFormOutputHelper::prepareCustomLink(['link' => $linkValue], true);

if ($url) {
    echo '<p>'
        . '<a class="btn btn-primary" href="' . rex_escape($url) . '"' . $linkData['customlink_target'] . '>'
        . rex_escape($linkData['customlink_text'])
        . '</a>'
        . '</p>';
}
```

---

## Schritt 4 – Mehrere Blöcke: der Repeater

Bisher hat das Modul genau einen Bild-Text-Block. Was, wenn der Redakteur **beliebig viele solcher Blöcke** anlegen soll? Dafür gibt es den Repeater: er verwaltet eine Liste von Einträgen, die der Redakteur hinzufügen, umsortieren und löschen kann.

Dazu wird das Formular für **einen Block** zuerst als Vorlage gebaut (`$rowForm`) und dann in den Repeater gesteckt.

### input.inc

```php
<?php
use FriendsOfRedaxo\MForm;

// Vorlage für einen einzigen Block (Bild links, Titel + Text rechts).
// addColumnElement(6, ...) teilt die Zeile in zwei gleichbreite Spalten auf (Bootstrap-Prinzip).
$rowForm = MForm::factory()
    ->addColumnElement(6,
        MForm::factory()
            ->addMediaField('image', ['label' => 'Bild']),
        MForm::factory()
            ->addTextField('title', ['label' => 'Titel'])
            ->addTextAreaField('text', ['label' => 'Inhalt', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    );

echo MForm::factory()
    // addRepeaterElement() nimmt die Blockvorlage und macht sie wiederholbar.
    // Slot 1 speichert die komplette Liste als JSON.
    ->addRepeaterElement(1, $rowForm, true, true, [
        'label'           => 'Inhaltsblöcke',
        'btn_text'        => 'Block hinzufügen',
        'collapsed'       => true,   // Einträge standardmäßig zugeklappt – spart Platz
        'first_open'      => true,   // erster Eintrag direkt aufgeklappt
        'show_toggle_all' => true,   // "Alle auf-/zuklappen"-Button
    ])
    ->show();
```

### output.inc

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// MFormRepeaterHelper::decode() wandelt das gespeicherte JSON in ein PHP-Array um.
// Das Ergebnis ist eine Liste von Blöcken – jeder Block ein assoziatives Array.
$blocks = MFormRepeaterHelper::decode('REX_VALUE[id=1]');

foreach ($blocks as $block) {
    $image = (string) ($block['image'] ?? '');
    $title = (string) ($block['title'] ?? '');
    $text  = (string) ($block['text']  ?? '');

    echo '<section class="content-block">';
    if ($image) {
        echo '<div class="content-block__image">'
            . '<img src="' . rex_url::media($image) . '" alt="' . rex_escape($title) . '">'
            . '</div>';
    }
    echo '<div class="content-block__body">';
    if ($title) {
        echo '<h3>' . rex_escape($title) . '</h3>';
    }
    if ($text) {
        echo '<div class="wysiwyg-content">' . $text . '</div>';
    }
    echo '</div></section>';
}
```

---

## Schritt 5 – Weiterführende Links pro Block

Manchmal soll jeder Inhaltsblock eine **Liste von Links** auf verwandte Artikel tragen.
Die Linklist funktioniert wie der Medienpool-Picker, nur für REDAXO-Artikel: der Redakteur sucht Artikel und fügt sie der Liste hinzu.

### input.inc

```php
<?php
use FriendsOfRedaxo\MForm;

// Jetzt ohne zweispaltiges Layout – einfach alle Felder untereinander:
$rowForm = MForm::factory()
    ->addMediaField('image', ['label' => 'Bild'])
    ->addTextField('title', ['label' => 'Titel'])
    ->addTextAreaField('text', ['label' => 'Inhalt', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    // addLinklistField() öffnet einen Artikel-Picker, mit dem mehrere Artikel gewählt werden können.
    // 'category' => 0 erlaubt Artikel aus allen Kategorien.
    ->addLinklistField('links', ['category' => 0], null, ['label' => 'Weiterführende Links']);

echo MForm::factory()
    ->addRepeaterElement(1, $rowForm, true, true, [
        'label'      => 'Inhaltsblöcke',
        'btn_text'   => 'Block hinzufügen',
        'collapsed'  => true,
        'first_open' => true,
    ])
    ->show();
```

### output.inc

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$blocks = MFormRepeaterHelper::decode('REX_VALUE[id=1]');

foreach ($blocks as $block) {
    echo '<section class="content-block">';
    echo '<h3>' . rex_escape((string) ($block['title'] ?? '')) . '</h3>';
    echo '<div class="wysiwyg-content">' . ((string) ($block['text'] ?? '')) . '</div>';

    // Die Linklist speichert Artikel-IDs als kommaseparierte Liste, z. B. "12,47,83".
    // array_filter() entfernt leere Einträge (z. B. wenn gar keine Links gesetzt sind).
    $ids = array_filter(array_map('trim', explode(',', (string) ($block['links'] ?? ''))));
    if ($ids) {
        echo '<ul class="content-block__links">';
        foreach ($ids as $id) {
            // rex_article::get() lädt den Artikel anhand seiner ID.
            $article = rex_article::get((int) $id);
            if ($article) {
                // rex_getUrl() liefert die richtige URL des Artikels.
                echo '<li><a href="' . rex_getUrl($article->getId()) . '">'
                    . rex_escape($article->getName())
                    . '</a></li>';
            }
        }
        echo '</ul>';
    }
    echo '</section>';
}
```

---

## Schritt 6 – Alles zusammen: Tabs, Header, CTA

Jetzt bauen wir das vollständige Seitenpflege-Modul. Die vielen Felder werden mit **Tabs** strukturiert, damit das Backend übersichtlich bleibt. Ein Tab für den Seitenkopf, einer für den Inhalt, einer für CTA und SEO.

### input.inc

```php
<?php
use FriendsOfRedaxo\MForm;

// Vorlage für jeden Inhaltsblock (wie in Schritt 5):
$rowForm = MForm::factory()
    ->addTextField('title', ['label' => 'Block-Titel'])
    ->addTextAreaField('text', ['label' => 'Block-Inhalt', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addLinklistField('links', ['category' => 0], null, ['label' => 'Linkliste']);

echo MForm::factory()
    // ── Tab 1: Basisdaten der Seite ───────────────────────────────────────────
    // Das dritte Argument 'true' macht diesen Tab standardmäßig aktiv (geöffnet).
    ->addTabElement('Basis', MForm::factory()
        ->addTextField(1, ['label' => 'Seitentitel'])
        ->addTextField(2, ['label' => 'Untertitel'])
        // addImagelistField() erlaubt mehrere Bilder – gut für einen Header-Slider.
        ->addImagelistField(3, ['label' => 'Headerbilder'])
    , true)
    // ── Tab 2: Die eigentlichen Inhaltsblöcke ─────────────────────────────────
    ->addTabElement('Inhalt', MForm::factory()
        ->addRepeaterElement(4, $rowForm, true, true, [
            'label'           => 'Inhaltsblöcke',
            'btn_text'        => 'Block hinzufügen',
            'collapsed'       => true,
            'first_open'      => true,
            'show_toggle_all' => true,
        ])
    )
    // ── Tab 3: Aktions-Button und SEO ─────────────────────────────────────────
    ->addTabElement('CTA & SEO', MForm::factory()
        ->addCustomLinkField(5, [
            'label'       => 'Haupt-CTA (Button)',
            'data-intern' => 'enable',
            'data-extern' => 'enable',
            'data-media'  => 'enable',
            'data-mailto' => 'enable',
            'data-tel'    => 'enable',
        ])
        ->addTextAreaField(6, ['label' => 'Meta Description'])
    )
    ->show();
```

### output.inc

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Werte aus den Slots lesen:
$title        = 'REX_VALUE[id=1]';
$subtitle     = 'REX_VALUE[id=2]';
// REX_MEDIALIST liefert eine kommaseparierte Liste von Dateinamen (für mehrere Bilder).
$headerImages = array_filter(explode(',', 'REX_MEDIALIST[id=3]'));
$blocks       = MFormRepeaterHelper::decode('REX_VALUE[id=4]');
$ctaValue     = 'REX_VALUE[id=5]';

// ── Seitenkopf ────────────────────────────────────────────────────────────────
echo '<header class="page-header">';
echo '<h1>' . rex_escape($title) . '</h1>';
if ($subtitle) {
    echo '<p class="page-subtitle">' . rex_escape($subtitle) . '</p>';
}
if ($headerImages) {
    echo '<div class="page-header__gallery">';
    foreach ($headerImages as $file) {
        echo '<img src="' . rex_url::media($file) . '" alt="' . rex_escape($title) . '">';
    }
    echo '</div>';
}
echo '</header>';

// ── Inhaltsblöcke ─────────────────────────────────────────────────────────────
echo '<main class="page-content">';
foreach ($blocks as $block) {
    echo '<section class="content-block">';
    echo '<h2>' . rex_escape((string) ($block['title'] ?? '')) . '</h2>';
    echo '<div class="wysiwyg-content">' . ((string) ($block['text'] ?? '')) . '</div>';

    $ids = array_filter(array_map('trim', explode(',', (string) ($block['links'] ?? ''))));
    if ($ids) {
        echo '<ul class="content-block__links">';
        foreach ($ids as $id) {
            $article = rex_article::get((int) $id);
            if ($article) {
                echo '<li><a href="' . rex_getUrl($article->getId()) . '">'
                    . rex_escape($article->getName())
                    . '</a></li>';
            }
        }
        echo '</ul>';
    }
    echo '</section>';
}
echo '</main>';

// ── Call-to-Action-Button ─────────────────────────────────────────────────────
$ctaUrl  = MFormOutputHelper::getCustomUrl($ctaValue);
$ctaData = MFormOutputHelper::prepareCustomLink(['link' => $ctaValue], true);
if ($ctaUrl) {
    echo '<p class="page-cta">'
        . '<a class="btn btn-primary" href="' . rex_escape($ctaUrl) . '"' . $ctaData['customlink_target'] . '>'
        . rex_escape($ctaData['customlink_text'])
        . '</a>'
        . '</p>';
}
```

---

## Was hast du jetzt gebaut?

Nach diesen sechs Schritten besitzt du ein Modul, das echte Seitenpflege-Arbeit stemmen kann:

| Feature | Schritt |
|---------|---------|
| Bild + Überschrift + Text | 1 |
| Formatierter Text (WYSIWYG) | 2 |
| Button mit internem/externem Link | 3 |
| Beliebig viele Inhaltsblöcke | 4 |
| Artikellinks je Block | 5 |
| Tab-Navigation, Header, CTA, SEO | 6 |

Du kannst das Modul jederzeit erweitern. Ideen für die nächsten Schritte:

- **Nested Repeater** – z. B. eine Schritt-für-Schritt-Anleitung mit mehreren Abschnitten, die jeweils eigene Untereinträge haben.
- **Darstellungsoptionen** – mit `addRadioImgField()` oder `addRadioColorField()` kann der Redakteur Layout und Farbe des Blocks direkt im Backend wählen.
- **Copy & Paste** – mit `'copy_paste' => true` am Repeater können Blöcke dupliziert werden, was die Arbeit bei ähnlichen Inhalten enorm beschleunigt.