# Mform und Mblock

Diese Seite beschreibt den pragmatischen Umgang mit bestehenden MBlock-Modulen im Zusammenspiel mit MForm 9.

1. Hinweise bei Weiterverwendung von MBlock
2. Konverter: was er kann und wie er genutzt wird
3. Manuelle Migration auf den MForm-Repeater

## 1) Hinweise bei Weiterverwendung von MBlock

Wenn Module vorerst bei MBlock bleiben sollen, gibt es ein paar wichtige Praxisregeln.

### Was weiterhin möglich ist

- Bestehende MBlock-Module können unverändert weiterlaufen.
- Für neue oder angepasste Module kann optional der Compat-Modus aktiviert werden.
- Das REDAXO-Speicherformat bleibt dabei erhalten (`REX_MEDIA_n`, `REX_LINK_n`).

### Wichtiger Hinweis zu klassischen Linklist-/Medialist-Widgets

- Die klassischen REDAXO-Widgets für `linklist` und `medialist` sind im MBlock-Kontext historisch nicht zuverlässig, vor allem bei Reindexing- oder Clone-Szenarien.
- Wenn du in MBlock robuste Link-/Medien-Felder brauchst, nutze bevorzugt die MForm-eigenen Custom-Widgets.

### Compat-Modus mit MForm

Mit MForm 9+ kannst du klassische Felder intern über das robustere `custom_link`-Widget rendern:

```php
<?php
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MBlock\MBlock;

MForm::useCustomLinkForClassicWidgets(true);

$id = 1;
$mform = MForm::factory()
    ->addTextField("$id.0.title", ['label' => 'Titel'])
    ->addMediaField(1, ['label' => 'Bild'])
    ->addLinkField(2, ['label' => 'Link']);

echo MBlock::show($id, $mform->show());

MForm::useCustomLinkForClassicWidgets(false);
```

Wichtig:

- `useCustomLinkForClassicWidgets(true)` ist ein globales Flag für den aktuellen Request.
- Nach dem betroffenen Formularbereich wieder auf `false` zurücksetzen.

### Kurze Entscheidungshilfe

- Neues MBlock-Modul, aber höhere Widget-Stabilität gewünscht: Compat-Modus aktivieren.
- Bestehendes Modul ohne Umbau und mit produktiven Altwerten: bisheriges Verhalten beibehalten.
- Geplante Umstellung auf Repeater: Compat-Modus als Übergang nutzen, dann sauber migrieren.

## 2) Konverter: was er kann und wie es geht

Im Backend unter `MForm -> MBlock zu Repeater` steht ein geführter Konverter-Workflow bereit.

### Was der Konverter kann

- Eingabe-Code von typischen MBlock-Mustern auf Repeater-Struktur umschreiben.
- Ausgabe-Code auf `MFormRepeaterHelper::decode()`-basierten Zugriff vorbereiten.
- Daten als Vorschau konvertieren (Dry-Run).
- Optional ein neues Modul mit Prefix `mfr_` erzeugen.
- Legacy-Key-Mapping unterstützen (z. B. `1 -> link`).
- Slices auf ein neues Modul umhängen und bei Bedarf wieder zurücksetzen.

### Grenzen des Konverters

Der Konverter ist bewusst deterministisch und textbasiert. Deshalb erkennt er nicht jede projektspezifische Sonderlogik, z. B.:

- individuelle Includes oder externe Helper-Dateien
- stark verschachtelte Spezialstrukturen
- proprietäre Feldkonventionen ohne eindeutiges Mapping

### Empfohlener Ablauf

1. Modul laden und Eingabe-/Ausgabe-Code konvertieren.
2. Optional ein neues Modul erzeugen.
3. Datenmigration als Dry-Run prüfen.
4. Legacy-Key-Mapping setzen (z. B. `1 -> link` oder JSON-Mapping).
5. Daten anwenden und Slices auf das Zielmodul umhängen.
6. Bei Bedarf die letzte Umhängung über Reassign-Revert zurücksetzen.

### Mapping im Tool

- Feld `Legacy-Key "1" auf Feldname mappen` für den häufigsten Altfall.
- Optionales JSON-Mapping für mehrere Keys, z. B.:

```json
{"1":"link","REX_LINK_1":"link"}
```

### Empfehlung für sichere Durchführung

1. Immer mit Dry-Run starten.
2. Danach auf Staging testen.
3. Erst dann produktiv anwenden.

## 3) Manuelle Migration

Wenn der Konverter nicht alle Besonderheiten deines Moduls abdecken kann, ist die manuelle Migration der zuverlässige Weg.

## Zielbild in MForm 9

- Wiederholende Inhalte laufen über `addRepeaterElement(...)`.
- Speicherung erfolgt als JSON in `REX_VALUE`.
- Der Aktiv/Inaktiv-Status pro Item wird intern über `__disabled` gespeichert.
- Für die Frontend-Ausgabe wird `MFormRepeaterHelper::decode()` genutzt.

### Schritt 1: Altmodul inventarisieren

Prüfe pro Modul:

- Welche Felder sind je MBlock-Item enthalten?
- Gibt es verschachtelte Blöcke?
- Welche alten Schlüssel werden verwendet (z. B. numerisch)?

Empfehlung: Im neuen Repeater sprechende Feldnamen einsetzen (`title`, `text`, `image`, `link`) statt rein numerischer Keys.

### Schritt 2: Eingabe auf Repeater umbauen

Beispiel für ein neues Repeater-Subformular:

```php
<?php
use FriendsOfRedaxo\MForm;

$itemForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addTextAreaField('text', ['label' => 'Text'])
    ->addMediaField('image', ['label' => 'Bild'])
    ->addCustomLinkField('link', ['label' => 'Link']);

echo MForm::factory()
    ->addRepeaterElement(1, $itemForm, true, true, [
        'label' => 'Einträge',
        'btn_text' => 'Eintrag hinzufügen',
        'max' => 100,
        'copy_paste' => true,
    ])
    ->show();
```

### Schritt 3: Ausgabe auf Repeater-Helper umstellen

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$items = MFormRepeaterHelper::decode(1);

foreach ($items as $item) {
    $title = (string) ($item['title'] ?? '');
    $text = (string) ($item['text'] ?? '');

    $link = MFormOutputHelper::createLinkData($item['link'] ?? '');

    echo '<h3>' . rex_escape($title) . '</h3>';
    echo '<div>' . $text . '</div>';

    if ('' !== $link['customlink_url']) {
        echo '<a href="' . rex_escape($link['customlink_url']) . '"'
            . $link['customlink_target']
            . '>' . rex_escape($link['customlink_text']) . '</a>';
    }
}
```

Hinweis: Link-Felder können je nach MForm-Version als String oder als Array vorliegen. `createLinkData()` normalisiert beide Varianten.

### Schritt 4: Bestehende Daten migrieren (optional)

Es gibt zwei Strategien:

1. Soft-Migration: neue Eingabe ab jetzt über Repeater, Alt-Datensätze bleiben bis zur Neuspeicherung.
2. Hart-Migration: einmalige Konvertierung alter Werte in das neue Repeater-JSON.

Empfehlung:

1. Erst Feldmapping pro Modul definieren.
2. Dann Dry-Run ausführen.
3. Danach mit Backup auf Staging testen.
4. Erst anschließend produktiv schreiben.

### Schritt 5: Checkliste nach der Migration

- Eingabe vollständig auf `addRepeaterElement(...)` umgestellt.
- Frontend-Ausgabe über `MFormRepeaterHelper::decode()` umgestellt.
- Link-Felder über `MFormOutputHelper::createLinkData()` oder `normalizeRepeaterItems()` normalisiert.
- Repeater-Aktionen getestet: Anlegen, Sortieren, Kopieren, Löschen, Speichern.
- Bei Nested-Strukturen auch verschachtelte Repeater getestet.
- Falls MBlock noch parallel läuft: `useCustomLinkForClassicWidgets` bewusst gesetzt oder bewusst nicht gesetzt.
