# MForm-Ausgabe mit REDAXO-Fragmenten und mfragment-Komponenten

MForm liefert strukturierte Werte. Die eigentliche Ausgabe sollte in Fragmente ausgelagert werden.

So bleibt dein Modul-Output klein, testbar und wartbar.

## Warum Fragmente?

- klare Trennung von Logik und Markup
- Wiederverwendung über mehrere Module hinweg
- bessere Lesbarkeit im Output-Code
- weniger Copy-Paste bei Varianten

Kurz: Denke in Komponenten, nicht in langen HTML-Strings.

## Zwei Wege: REDAXO-Fragmente und mfragment

### 1) REDAXO-Fragmente im Projekt

Lege Fragment-Dateien im Projekt als wiederverwendbare Bausteine ab (zum Beispiel in `fragments/modules/...`).

Im Modul-Output:

1. MForm-Werte vorbereiten
2. Daten in ein Fragment geben
3. Fragment rendern

```php
<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$headline = 'REX_VALUE[id=1]';
$items = MFormRepeaterHelper::decode('REX_VALUE[id=2 output="html"]');

// Link-Felder in Repeater-Items vereinheitlichen
$items = MFormOutputHelper::normalizeRepeaterItems($items, ['link']);

$fragment = new rex_fragment();
$fragment->setVar('headline', $headline, false);
$fragment->setVar('items', $items, false);

echo $fragment->parse('modules/teaser/list.php');
```

Beispiel-Fragment `modules/teaser/list.php`:

```php
<?php

$headline = (string) $this->getVar('headline', '');
$items = (array) $this->getVar('items', []);

if ('' !== $headline) {
    echo '<h2>' . rex_escape($headline) . '</h2>';
}

if ([] === $items) {
    return;
}

echo '<ul class="teaser-list">';
foreach ($items as $item) {
    $title = (string) ($item['title'] ?? '');
    $link = (array) ($item['link_normalized'] ?? []);
    $url = (string) ($link['customlink_url'] ?? '');
    $target = (string) ($link['customlink_target'] ?? '');

    if ('' === $title) {
        continue;
    }

    echo '<li>';
    if ('' !== $url) {
        echo '<a href="' . rex_escape($url) . '"' . $target . '>' . rex_escape($title) . '</a>';
    } else {
        echo rex_escape($title);
    }
    echo '</li>';
}
echo '</ul>';
```

### 2) mfragment AddOn

Wenn mfragment verfügbar ist, nutze die Komponenten-API direkt.

Referenz:
https://github.com/FriendsOfREDAXO/mfragment

Wichtig:

- Für neue Implementierungen `show()` verwenden
- `MFragment::parse()` ist nur Legacy/Rückwärtskompatibilität

Korrektes mfragment-Beispiel (Komponenten-API):

```php
<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
use FriendsOfRedaxo\MFragment;
use FriendsOfRedaxo\MFragment\Components\Bootstrap\Card;
use FriendsOfRedaxo\MFragment\Components\Default\Figure;

$rows = MFormRepeaterHelper::decode('REX_VALUE[id=2 output="html"]');
$rows = MFormOutputHelper::normalizeRepeaterItems($rows, ['link']);

$grid = MFragment::factory()->addClass('row g-4');

foreach ($rows as $row) {
    $title = (string) ($row['title'] ?? '');
    $text = (string) ($row['text'] ?? '');
    $image = (string) ($row['image'] ?? '');
    $link = (array) ($row['link_normalized'] ?? []);
    $url = (string) ($link['customlink_url'] ?? '');

    $cardBody = '';
    if ('' !== $title) {
        $cardBody .= '<h3 class="h5">' . rex_escape($title) . '</h3>';
    }
    if ('' !== $text) {
        $cardBody .= '<p>' . rex_escape($text) . '</p>';
    }
    if ('' !== $url) {
        $cardBody .= '<p><a class="btn btn-primary" href="' . rex_escape($url) . '">Mehr</a></p>';
    }

    $card = Card::factory()->setBody($cardBody)->addClass('h-100');

    if ('' !== $image) {
        $figure = Figure::factory()
            ->setMedia($image)
            ->setMediaManagerType('full_16x9')
            ->enableAutoResponsive();

        $card->setImage($figure);
    }

    $grid->addDiv($card, ['class' => 'col-12 col-md-6 col-lg-4']);
}

echo $grid->show();
```

MForm und mfragment ergänzen sich ideal:

- MForm strukturiert und validiert Eingabedaten
- mfragment rendert wiederverwendbare Ausgabe-Komponenten

Mfragment und Mform - ein unschlagbares Duo ;-)

## Empfohlene Ordnerstruktur

```text
fragments/
  modules/
    teaser/
      list.php
      item.php
    hero/
      default.php
      image-left.php
```

Bei mfragment entsprechend analog mit komponentenorientierter Struktur arbeiten.

## Ausgabe-Pattern (Best Practice)

Halte den Output immer in drei Schritten:

1. Daten lesen (`REX_VALUE`, Repeater-Decode)
2. Daten normalisieren (z. B. Links, Defaults, Sortierung)
3. Komponenten rendern (Fragmente)

So vermeidest du, dass Fachlogik in HTML-Schleifen landet.

## Repeater + Komponenten

Gerade bei Repeatern lohnt sich die Fragment-Aufteilung pro Item.

Modul-Output:

```php
<?php

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$rows = MFormRepeaterHelper::decode('REX_VALUE[id=3 output="html"]');

foreach ($rows as $row) {
    $item = new rex_fragment();
    $item->setVar('item', $row, false);
    echo $item->parse('modules/teaser/item.php');
}
```

Das hält jede Komponente klein und unabhängig.

## Checkliste

- keine langen HTML-Blöcke direkt im Modul-Output
- wiederkehrende Markup-Teile in Fragment-Dateien auslagern
- Repeater-Daten immer über `MFormRepeaterHelper::decode()` vorbereiten
- Link-Felder bei Bedarf über `MFormOutputHelper::normalizeLinkData()` oder `normalizeRepeaterItems()` vereinheitlichen
- Fragmente mit klaren Eingabe-Variablen (`setVar`) bauen

## Fazit

MForm sorgt für saubere Datenstruktur in der Eingabe.
Fragmente sorgen für saubere Architektur in der Ausgabe.

Komponentenorientierte Ausgabe macht Module langlebig, verständlich und leichter erweiterbar.
