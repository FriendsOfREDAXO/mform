# Migration von MBlock zu MForm 9

Diese Anleitung zeigt den pragmatischen Weg, bestehende MBlock-Module auf den MForm-9-Repeater umzustellen.

## Zielbild in MForm 9

- Wiederholende Inhalte laufen ueber `addRepeaterElement(...)`.
- Speicherung erfolgt als JSON in `REX_VALUE`.
- Der Aktiv/Inaktiv-Status pro Item wird intern ueber `__disabled` gespeichert.
- Fuer die Ausgabe im Frontend wird `MFormRepeaterHelper::decode()` empfohlen.

## Schritt 1: Altes MBlock-Modul inventarisieren

Pruefe pro Modul:

- Welche Felder sind im MBlock-Eintrag enthalten?
- Gibt es verschachtelte Bloecke?
- Welche IDs werden aktuell genutzt (z. B. `1.0`, `1.1`, ...)?

Tipp: Die Feldnamen im neuen Repeater sollten semantisch sein (`title`, `text`, `image`) statt rein numerisch.

## Schritt 2: Repeater-Form in MForm bauen

### Vorher (MBlock-Prinzip, schematisch)

```php
<?php
// Beispielhaft: alte MBlock-Logik
// $MBlock->addTextField("$id.0.1", ['label' => 'Titel']);
// $MBlock->addMediaField("$id.0.2", ['label' => 'Bild']);
```

### Nachher (MForm 9)

```php
<?php
use FriendsOfRedaxo\MForm;

$itemForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addTextAreaField('text', ['label' => 'Text'])
    ->addMediaField('image', ['label' => 'Bild']);

echo MForm::factory()
    ->addRepeaterElement(1, $itemForm, true, true, [
        'label'           => 'Eintraege',
        'btn_text'        => 'Eintrag hinzufuegen',
        'min'             => 0,
        'max'             => 50,
        'collapsed'       => true,
        'first_open'      => true,
        'show_toggle_all' => true,
    ])
    ->show();
```

## Schritt 3: Frontend-Ausgabe auf Repeater-Helper umstellen

### Empfohlen (MForm 9)

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$items = MFormRepeaterHelper::decode('REX_VALUE[id=1 output=json]');

foreach ($items as $item) {
    echo '<h3>' . rex_escape($item['title'] ?? '') . '</h3>';
    echo '<div>' . ($item['text'] ?? '') . '</div>';
}
```

`decode()` uebernimmt:

- JSON + Entity-Decoding
- Entfernung deaktivierter Items (`__disabled`)
- Rekursive Aufbereitung bei Nested-Repeatern

## Schritt 4: Nested-Bloecke migrieren

Wenn ein MBlock Unterbloecke hatte, in MForm als verschachtelten Repeater modellieren:

```php
<?php
use FriendsOfRedaxo\MForm;

$childForm = MForm::factory()
    ->addTextField('item', ['label' => 'Unterpunkt']);

$parentForm = MForm::factory()
    ->addTextField('headline', ['label' => 'Ueberschrift'])
    ->addRepeaterElement('children', $childForm, true, true, [
        'label' => 'Unterpunkte',
    ]);

echo MForm::factory()
    ->addRepeaterElement(1, $parentForm)
    ->show();
```

## Schritt 5: Datenmigration (optional)

Wenn bestehende Inhalte bereits in MBlock-Struktur gespeichert sind, gibt es zwei Wege:

1. Soft-Migration: Nur Eingabe ab jetzt ueber MForm; alte Datensaetze bleiben, bis sie im Backend neu gespeichert wurden.
2. Hart-Migration: Ein einmaliges Skript konvertiert alte Werte in das neue Repeater-JSON-Format.

Empfehlung: Zuerst ein Mapping pro Modul erstellen (alt -> neu), dann auf einer Staging-Kopie testen.

### Beispielskript (Dry-Run + Write)

Das folgende Beispiel migriert einen alten Wert aus `value1` (MBlock-Altformat) in ein neues Repeater-JSON nach `value2`.
Passe `MODULE_ID`, Quell-/Zielfeld und die Mapping-Funktion an dein Modul an.

```php
<?php

/**
 * Beispiel: Einmalige Migration fuer ein REDAXO-Modul.
 *
 * Aufruf im Projektkontext, z. B. ueber ein temporaeres CLI/Install-Skript.
 * Vorher immer DB-Backup erstellen.
 */

use rex;
use rex_sql;

// ------------------------------------------------------------
// Konfiguration
// ------------------------------------------------------------
const MODULE_ID = 123;         // Ziel: nur Slices dieses Moduls
const SOURCE_COLUMN = 'value1'; // Altformat (MBlock)
const TARGET_COLUMN = 'value2'; // Neuformat (MForm-Repeater JSON)
const DRY_RUN = true;           // true = nur pruefen, false = schreiben

/**
 * Konvertiert einen alten MBlock-Datensatz in das neue Repeater-Format.
 *
 * Erwartet ein Array alter Bloecke und liefert ein Array neuer Repeater-Items.
 *
 * @param array<int, array<string, mixed>> $legacyBlocks
 * @return array<int, array<string, mixed>>
 */
function mapLegacyBlocksToMForm(array $legacyBlocks): array
{
    $items = [];

    foreach ($legacyBlocks as $block) {
        // Beispiel-Mapping: alte numerische Keys -> neue sprechende Keys
        $title = isset($block['1']) ? (string) $block['1'] : '';
        $text = isset($block['2']) ? (string) $block['2'] : '';
        $image = isset($block['3']) ? (string) $block['3'] : '';

        $items[] = [
            'title' => $title,
            'text' => $text,
            'image' => $image,
            // '__disabled' => 1, // optional, falls noetig
        ];
    }

    return $items;
}

/**
 * Liest altes Payload-Format.
 *
 * Typische Altdaten sind JSON oder serialisierte PHP-Arrays.
 * Passe die Erkennung bei Bedarf fuer dein Projekt an.
 *
 * @return array<int, array<string, mixed>>
 */
function parseLegacyPayload(string $raw): array
{
    $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }

    $unserialized = @unserialize($raw, ['allowed_classes' => false]);
    if (is_array($unserialized)) {
        return $unserialized;
    }

    return [];
}

$sql = rex_sql::factory();
$table = rex::getTable('article_slice');
$rows = $sql->getArray('SELECT id, ' . SOURCE_COLUMN . ' FROM ' . $table . ' WHERE module_id = :moduleId', [
    'moduleId' => MODULE_ID,
]);

$total = count($rows);
$changed = 0;

echo "Gefundene Slices: {$total}\n";

foreach ($rows as $row) {
    $sliceId = (int) $row['id'];
    $raw = (string) $row[SOURCE_COLUMN];

    if ('' === trim($raw)) {
        continue;
    }

    $legacyBlocks = parseLegacyPayload($raw);
    if ([] === $legacyBlocks) {
        echo "[WARN] Slice {$sliceId}: Altdaten nicht lesbar\n";
        continue;
    }

    $newItems = mapLegacyBlocksToMForm($legacyBlocks);
    $newJson = json_encode($newItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

    if (false === $newJson) {
        echo "[WARN] Slice {$sliceId}: JSON-Encoding fehlgeschlagen\n";
        continue;
    }

    if (DRY_RUN) {
        echo "[DRY] Slice {$sliceId}: " . count($newItems) . " Items vorbereitet\n";
        $changed++;
        continue;
    }

    $update = rex_sql::factory();
    $update->setTable($table);
    $update->setValue(TARGET_COLUMN, $newJson);
    $update->setWhere(['id' => $sliceId]);
    $update->update();

    echo "[OK] Slice {$sliceId}: geschrieben\n";
    $changed++;
}

echo "Fertig. Geaenderte Slices: {$changed}\n";
```

Hinweise zum Einsatz:

- Erst mit `DRY_RUN = true` laufen lassen und Ausgabe pruefen.
- Danach mit `DRY_RUN = false` schreiben.
- Fuer produktive Migration immer Backup + Staging-Test.
- Nach der Migration Frontend-Ausgabe auf `MFormRepeaterHelper::decode()` umstellen.

### Konkretes Beispiel mit deinem Karten-Modul

Die folgende Reihenfolge entspricht genau dem praktischen Vorgehen:

1. Altes Modul (gekuerzt, MBlock-basiert)
2. Datenmigration
3. Neues Eingabemodul (MForm 9 Repeater)
4. Beispielhafte Ausgabeverarbeitung

#### 1) Altes Modul (gekuerzt)

```php
<?php
use FriendsOfRedaxo\MForm;

$id = 1;

$cardBlock = MForm::factory()
    ->addTabElement('Inhalte', MForm::factory()
        ->addTextField("$id.0.header", ['label' => 'Kopfzeile'])
        ->addMediaField(1, ['label' => 'Bild oder Video', 'preview' => '1'])
        ->addTextField("$id.0.imageTitle", ['label' => 'Bilduntertitel'])
        ->addTextAreaField("$id.0.content", ['label' => 'Text', 'class' => 'cke5-editor'])
        ->addCustomLinkField("$id.0.1", ['label' => 'Link'])
        ->addTextField("$id.0.LinkText", ['label' => 'Linktext'])
    , true)
    ->addTabElement('Einstellungen', MForm::factory()
        ->addSelectField("$id.0.layout", ['media-top' => 'oben', 'media-left' => 'links'])
        ->addSelectField("$id.0.mediaWidth", ['1-3@m' => '33%', '1-2@m' => '50%'])
        ->addSelectField("$id.0.cardShadow", ['' => 'Standard', 'uk-shadow-remove' => 'Kein Schatten'])
    );

$blocks = MBlock::show($id, $cardBlock->show(), ['max' => 100]);

echo MForm::factory()
    ->addTabElement('Karten', MForm::factory()->addHtml($blocks), true)
    ->show();
```

#### 2) Datenmigration fuer dieses Modul

Annahme fuer dieses Modul:

- Repeater-Blockdaten liegen in `value1`.
- Du willst den neuen Repeater auch in `value1` speichern.
- Globale Einstellungen in anderen Feldern (`2.0.*`, `3.0.*`, `6.0.*`, `9.0.*`, `14.0.*`, `15.0.*`) bleiben unveraendert.

Feldmapping pro Karte:

- `header` -> `header`
- `image` -> `image`
- `imageTitle` -> `imageTitle`
- `imageAlt` -> `imageAlt`
- `content` -> `content`
- `1.0.1` (alt: `$id.0.1`) -> `link`
- `LinkText` -> `linkText`
- `layout` -> `layout`
- `mediaWidth` -> `mediaWidth`
- `cardShadow` -> `cardShadow`

Migrationsskript:

```php
<?php

use rex;
use rex_sql;

const MODULE_ID = 123; // ID des Karten-Moduls
const DRY_RUN = true;  // erst testen, dann false

/**
 * Wert aus Legacy-Block lesen.
 * Unterstuetzt mehrere moegliche Key-Varianten je nach Altdaten.
 *
 * @param array<string, mixed> $block
 */
function pick(array $block, string ...$keys): string
{
    foreach ($keys as $k) {
        if (isset($block[$k]) && '' !== trim((string) $block[$k])) {
            return (string) $block[$k];
        }
    }
    return '';
}

/**
 * @param array<string, mixed> $block
 * @return array<string, mixed>
 */
function mapCardBlock(array $block): array
{
    return [
        'header' => pick($block, 'header', '1.0.header', '0.header'),
        // Je nach Altstruktur kann das Medienfeld als "image" oder numerischer Key vorliegen.
        'image' => pick($block, 'image', '1.0.image', '0.image', 'media', '1.0.media', '0.media', '1'),
        'imageTitle' => pick($block, 'imageTitle', '1.0.imageTitle', '0.imageTitle'),
        'imageAlt' => pick($block, 'imageAlt', '1.0.imageAlt', '0.imageAlt'),
        'content' => pick($block, 'content', '1.0.content', '0.content'),

        // Wichtig: NICHT den nackten Key "1" verwenden (kann mit image kollidieren).
        // Altes Feld "$id.0.1" (Custom-Link) -> neues Feld "link"
        'link' => pick($block, '1.0.1', '0.1', 'link'),
        'linkText' => pick($block, 'LinkText', '1.0.LinkText', '0.LinkText', 'linkText'),

        'layout' => pick($block, 'layout', '1.0.layout', '0.layout', 'media-top'),
        'mediaWidth' => pick($block, 'mediaWidth', '1.0.mediaWidth', '0.mediaWidth', '1-3@m'),
        'cardShadow' => pick($block, 'cardShadow', '1.0.cardShadow', '0.cardShadow'),
    ];
}

/**
 * @return array<int, array<string, mixed>>
 */
function parseLegacyBlocks(string $raw): array
{
    $raw = html_entity_decode($raw, ENT_QUOTES | ENT_HTML5, 'UTF-8');

    $json = json_decode($raw, true);
    if (is_array($json)) {
        return $json;
    }

    $unserialized = @unserialize($raw, ['allowed_classes' => false]);
    if (is_array($unserialized)) {
        return $unserialized;
    }

    return [];
}

$table = rex::getTable('article_slice');
$rows = rex_sql::factory()->getArray(
    'SELECT id, value1 FROM ' . $table . ' WHERE module_id = :moduleId',
    ['moduleId' => MODULE_ID]
);

echo 'Gefundene Slices: ' . count($rows) . PHP_EOL;

foreach ($rows as $row) {
    $sliceId = (int) $row['id'];
    $legacy = parseLegacyBlocks((string) $row['value1']);

    if ([] === $legacy) {
        echo '[WARN] Slice ' . $sliceId . ': Kein lesbares Altformat in value1' . PHP_EOL;
        continue;
    }

    $mapped = [];
    foreach ($legacy as $block) {
        if (!is_array($block)) {
            continue;
        }
        $mapped[] = mapCardBlock($block);
    }

    $newJson = json_encode($mapped, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if (false === $newJson) {
        echo '[WARN] Slice ' . $sliceId . ': JSON konnte nicht erstellt werden' . PHP_EOL;
        continue;
    }

    if (DRY_RUN) {
        echo '[DRY] Slice ' . $sliceId . ': ' . count($mapped) . ' Karten vorbereitet' . PHP_EOL;
        continue;
    }

    $update = rex_sql::factory();
    $update->setTable($table);
    $update->setValue('value1', $newJson);
    $update->setWhere(['id' => $sliceId]);
    $update->update();

    echo '[OK] Slice ' . $sliceId . ': value1 aktualisiert' . PHP_EOL;
}
```

Vor dem Schreiben einmal pruefen, welche Keys wirklich in den Altdaten stehen:

```php
<?php
$rows = rex_sql::factory()->getArray(
    'SELECT id, value1 FROM ' . rex::getTable('article_slice') . ' WHERE module_id = :moduleId LIMIT 5',
    ['moduleId' => MODULE_ID]
);

foreach ($rows as $row) {
    $legacy = parseLegacyBlocks((string) $row['value1']);
    $first = is_array($legacy) && isset($legacy[0]) && is_array($legacy[0]) ? $legacy[0] : [];
    echo 'Slice ' . (int) $row['id'] . ': ' . implode(', ', array_keys($first)) . PHP_EOL;
}
```

#### 3) Neuer Eingabe-Code (MForm 9)

Das alte `MBlock::show(...)` wird durch einen nativen Repeater ersetzt.
Die Karte liegt weiterhin in Feld `1`, aber mit sprechenden Feldnamen.

```php
<?php
use FriendsOfRedaxo\MForm;

$cardForm = MForm::factory()
    ->addTabElement('Inhalte', MForm::factory()
        ->addTextField('header', ['label' => 'Kopfzeile'])
        ->addMediaField('image', ['label' => 'Bild oder Video', 'preview' => '1'])
        ->addTextField('imageTitle', ['label' => 'Bilduntertitel'])
        ->addTextField('imageAlt', ['label' => 'ALT-Text'])
        ->addTextAreaField('content', ['label' => 'Text', 'class' => 'cke5-editor'])
        ->addCustomLinkField('link', ['label' => 'Link'])
        ->addTextField('linkText', ['label' => 'Linktext'])
    , true)
    ->addTabElement('Einstellungen', MForm::factory()
        ->addSelectField('layout', ['media-top' => 'oben', 'media-left' => 'links'])
        ->addSelectField('mediaWidth', ['1-3@m' => '33%', '1-2@m' => '50%'])
        ->addSelectField('cardShadow', ['' => 'Standard', 'uk-shadow-remove' => 'Kein Schatten'])
    );

$main = MForm::factory()
    ->addTabElement('Karten', MForm::factory()->addRepeaterElement(1, $cardForm, true, true, [
        'label' => 'Inhaltszeilen',
        'btn_text' => 'Zeile hinzufuegen',
        'max' => 100,
        'copy_paste' => true,
    ]), true)
    ->addTabElement('Card Einstellungen', MForm::factory()
        ->addSelectField('2.0.gutterWidth', ['medium' => 'normal', 'small' => 'eng', 'large' => 'weit'])
    );

echo $main->show();
```

#### 4) Beispielhafte Ausgabeverarbeitung

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$cards = MFormRepeaterHelper::decode('REX_VALUE[id=1 output=json]');

foreach ($cards as $card) {
    $title = (string) ($card['header'] ?? '');
    $text = (string) ($card['content'] ?? '');
    $layout = (string) ($card['layout'] ?? 'media-top');
    $mediaWidth = (string) ($card['mediaWidth'] ?? '1-3@m');
    $shadow = (string) ($card['cardShadow'] ?? '');

    $linkRaw = (string) ($card['link'] ?? '');
    $linkText = (string) ($card['linkText'] ?? 'Mehr erfahren');
    $href = MFormOutputHelper::getCustomUrl($linkRaw);

    echo '<article class="uk-card uk-card-default ' . rex_escape($shadow) . '">';
    echo '<div class="uk-card-body">';
    echo '<h3>' . rex_escape($title) . '</h3>';
    echo '<div class="uk-text-small">Layout: ' . rex_escape($layout) . ' / Breite: ' . rex_escape($mediaWidth) . '</div>';
    echo '<div>' . $text . '</div>';

    if ('' !== $href) {
        echo '<p><a class="uk-button uk-button-text" href="' . rex_escape($href) . '">' . rex_escape($linkText) . '</a></p>';
    }

    echo '</div>';
    echo '</article>';
}
```

Wichtig:

- Den MBlock-Teil durch `addRepeaterElement(1, $cardForm, ...)` ersetzen.
- Im Repeater-Subformular den alten Feldnamen `1` fuer den Link vermeiden und z. B. `link` nutzen.
- Falls du den Feldnamen aenderst, muss das Mapping oben entsprechend gleich bleiben (`'1' -> 'link'`).

## Stolperfallen

- Keine 1:1-Kopie von numerischen MBlock-IDs erzwingen. Besser sprechende Keys nutzen.
- Im Frontend keine rohe Repeater-JSON direkt verarbeiten, immer `decode()` nutzen.
- Bei TinyMCE in Repeatern die Klasse `tiny-editor` und ein gueltiges `data-profile` setzen.
- Bei Link-/Media-Listen moeglichst die MForm-Widgets nutzen (`addLinklistField`, `addMedialistField`).

## Kurze Checkliste

- Eingabe auf `addRepeaterElement(...)` umgestellt
- Feldstruktur pro Block definiert
- Frontend-Ausgabe auf `MFormRepeaterHelper::decode()` umgestellt
- Nested-Repeater getestet
- Bestehende Daten (falls noetig) migriert
- Modul im Redaktionsalltag getestet (Anlegen, Sortieren, Loeschen, Speichern)
