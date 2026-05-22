# Flex-Repeater

Das Flex-Repeater-Feld ermöglicht es Ihnen, eine Gruppe von Feldern zu erstellen, die beliebig oft wiederholt werden können. Das ist z.B. bei sich wiederholenden Informationen wie Ansprechpersonen, Leistungen und Layouts wie Tabellen, Listen, mehrspaltige Inhalte oder Reiter sinnvoll.

Der Flex-Repeater ist keine 1:1-Übernahme von MBlock, sondern ein moderner Ansatz, um wiederholende Inhalte zu erstellen. Er ist ein eigenständiges Element, das in MForm integriert ist.

In der Funktionalität deckt der Flex-Repeater inzwischen alle bekannten MBlock-Funktionen ab.

---

## Verfügbarkeit

| Methode | Klassisches Modul | `rex_form` | YForm |
|---|---|---|---|
| `addRepeaterElement` | ja | – | – |
| `addFlexRepeaterElement` | ja | – | – |

## Beispiele

### Eingabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm;

$formtorepeat = MForm::factory();
$formtorepeat->addFieldsetArea('fieldset1', MForm::factory()
->addTextField('item', ['label' => 'List-Item'])
);

$mform = MForm::factory();
$mform->addTextField(1, ['label' => 'Headline']);
$mform->addRepeaterElement(2, $formtorepeat);

echo $mform->show();
```

### Ausgabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

// decode() filtert deaktivierte Items und entfernt __disabled automatisch
$items = MFormRepeaterHelper::decode(2);
?>

<h1>REX_VALUE[1]</h1>

<ul>
    <?php foreach ($items as $item) : ?>
        <li><?php echo $item['item']; ?></li>
    <?php endforeach; ?>
</ul>
```

### `MFormRepeaterHelper::decode()` vs `prepareItemsForOutput()`

Ab Version 9 gibt es eine Kurzform für das Auslesen von Repeater-Werten:

> **Wann ist `decode()` nötig?**  
> `decode()` ist erforderlich, sobald der Repeater einen **Online/Offline-Toggle** (`__disabled`-Flag) verwendet – es filtert deaktivierte Items automatisch heraus.  
> Für einfache Repeater ohne Toggle-Funktion und bei der Migration bestehender Module ist `decode()` nicht zwingend erforderlich – `json_decode()` + `html_entity_decode()` reicht dort aus.

| Methode | Verwendung |
|---------|-----------|
| `decode(int\|string $source)` | **Empfohlen** – übernimmt Slot-Auflösung (bei `int`) sowie JSON-/Entity-Dekodierung und Item-Filterung in einem Schritt. |
| `prepareItemsForOutput(array $items)` | Wenn der Array bereits dekodiert vorliegt (z. B. aus einer DB-Abfrage). |

```php
// Neu (v9): kürzeste Form im Modul-Output
$rows = MFormRepeaterHelper::decode(1);

// Äquivalent mit prepareItemsForOutput (v8-kompatibel)
$raw   = json_decode(html_entity_decode('REX_VALUE[id=1]', ENT_QUOTES | ENT_HTML5, 'UTF-8'), true) ?? [];
$rows  = MFormRepeaterHelper::prepareItemsForOutput($raw);
```

Beide Wege verarbeiten verschachtelte Repeater rekursiv und unterstützen das `__disabled`-Flag korrekt.

### Frontend-Datenverarbeitung

Nach dem Dekodieren stehen weitere Hilfsmethoden für typische Ausgabeszenarien zur Verfügung:

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$items = MFormRepeaterHelper::decode(1);

// Nach Feldwert filtern
$news = MFormRepeaterHelper::filterByField($items, 'category', 'news');
// Mit striktem Vergleich (===)
$active = MFormRepeaterHelper::filterByField($items, 'status', '1', strict: true);

// Sortieren (asc / desc, numerisch oder alphabetisch automatisch erkannt)
$sorted = MFormRepeaterHelper::sortByField($items, 'date', 'desc');

// Gruppieren – liefert [gruppenname => [items]]
$grouped = MFormRepeaterHelper::groupByField($items, 'category');
foreach ($grouped as $category => $categoryItems) {
    echo '<h2>' . rex_escape($category) . '</h2>';
    foreach ($categoryItems as $item) {
        echo '<p>' . rex_escape($item['title'] ?? '') . '</p>';
    }
}

// Pagination
$perPage = 10;
$page    = (int) rex_get('p', 'int', 0);
$paged   = MFormRepeaterHelper::limitItems($items, $perPage, $page * $perPage);
```

---

## Nested Repeater mit TinyMCE

Repeater lassen sich verschachteln (Level 1 + Level 2). TinyMCE kann auf beiden Ebenen verwendet werden – der Repeater behandelt Destroy/Reinit automatisch beim Verschieben und Löschen.

### Eingabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm;

// Level-2-Formular: Textabschnitte je Schritt
$stepForm = MForm::factory()
    ->addTextField('title', ['label' => 'Schritt-Titel'])
    ->addTextAreaField('body', ['label' => 'Inhalt (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
;

// Level-1-Formular: Abschnitte mit eingebettetem Level-2-Repeater
$sectionForm = MForm::factory()
    ->addTextField('section_title', ['label' => 'Abschnitts-Titel'])
    ->addTextAreaField('intro', ['label' => 'Einleitung (TinyMCE)', 'class' => 'tiny-editor', 'data-profile' => 'default'])
    ->addRepeaterElement('steps', $stepForm, true, true, [
        'label'              => 'Schritte',
        'btn_text'           => 'Schritt hinzufügen',
        'confirm_delete'     => true,
        'confirm_delete_msg' => 'Schritt wirklich entfernen?',
        'min'                => 1,
        'max'                => 10,
    ])
;

$mform = MForm::factory()
    ->addTextField(1, ['label' => 'Seitentitel'])
    ->addRepeaterElement(2, $sectionForm, true, true, [
        'label'              => 'Abschnitte',
        'btn_text'           => 'Abschnitt hinzufügen',
        'confirm_delete'     => true,
        'confirm_delete_msg' => 'Abschnitt wirklich entfernen?',
        'collapsed'          => true,
        'first_open'         => true,
        'show_toggle_all'    => true,
    ])
;

echo $mform->show();
```

### Ausgabe-Modul

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;

$sections = MFormRepeaterHelper::decode(2);
?>

<h1><?= rex_escape('REX_VALUE[1]') ?></h1>

<?php foreach ($sections as $section) : ?>
<section>
    <h2><?= rex_escape($section['section_title'] ?? '') ?></h2>

    <?php if (!empty($section['intro'])) : ?>
        <div class="intro"><?= $section['intro'] ?></div>
    <?php endif; ?>

    <?php if (!empty($section['steps'])) : ?>
    <ol>
        <?php foreach ($section['steps'] as $step) : ?>
        <li>
            <h3><?= rex_escape($step['title'] ?? '') ?></h3>
            <?php if (!empty($step['body'])) : ?>
                <div class="step-body"><?= $step['body'] ?></div>
            <?php endif; ?>
        </li>
        <?php endforeach; ?>
    </ol>
    <?php endif; ?>
</section>
<?php endforeach; ?>
```

### Hinweise zu TinyMCE

- Die Textarea muss die CSS-Klasse `tiny-editor` erhalten, damit das TinyMCE-Addon sie erkennt.
- `data-profile` gibt das TinyMCE-Profil an (Standard: `default`).
- Beim Drag-and-Drop sowie beim Klick auf „Nach oben / Nach unten" wird TinyMCE automatisch korrekt destroyed und reinitialisiert – kein manueller Eingriff nötig.
- TinyMCE-Inhalte werden vor jedem DOM-Move in die Textarea zurückgeschrieben und nach der Neuinitialisierung wieder geladen.

## Editor-Kompatibilität (TinyMCE, CKE5, MarkdownEditor)

Der Flex-Repeater unterstützt gängige REDAXO-Editoren auch in dynamischen Repeater-Zeilen (Add, Remove, Move, Collapse).

### TinyMCE

```php
->addTextAreaField('text', [
    'label' => 'Text (TinyMCE)',
    'class' => 'tiny-editor',
    'data-profile' => 'default',
])
```

### CKE5

```php
->addTextAreaField('text', [
    'label' => 'Text (CKE5)',
    'class' => 'cke5-editor',
])
```

### MarkdownEditor

```php
->addTextAreaField('markdown', [
    'label' => 'Markdown',
    'class' => 'markdowneditor-editor',
    'data-markdowneditor-profile' => 'default',
])
```

### Voraussetzungen

- Das jeweilige Editor-Addon muss installiert und aktiviert sein.
- Die Initialisierung erfolgt durch das jeweilige Addon (MForm liefert die Textarea und Repeater-Lifecycle-Events).
- Ohne aktives Editor-Addon bleibt das Feld eine normale Textarea.

### Standardverhalten und Optionen

- Repeater-Items sind standardmäßig reduziert, das erste Item bleibt geöffnet.
- Im Header jedes Items gibt es einen "Danach hinzufügen"-Button.
- Im Header jedes Items gibt es ein Auge-Icon zum Aktivieren/Deaktivieren für die Ausgabe.
- Ist ein Item deaktiviert, bleibt es im Backend editierbar, wird aber in der Ausgabe über `MFormRepeaterHelper::decode()` (oder alternativ `prepareItemsForOutput()`) entfernt.
- Im Repeater-Toolbar gibt es optional einen "Alle auf / zu"-Button.

### Aktiv/Inaktiv (Auge) und Ausgabe

- Das Auge-Icon steuert den Status pro Item (aktiv/inaktiv).
- Inaktive Items werden als Metadaten mit dem Schlüssel `__disabled` im Repeater-JSON gespeichert.
- Für die Ausgabe sollte der Repeater-Wert bevorzugt über `MFormRepeaterHelper::decode()` laufen.
- Die Methode entfernt inaktive Items rekursiv (auch in nested Repeatern) und entfernt den Metaschlüssel aus der Ausgabe.

Verfügbare Optionen im Repeater-Array:

- `collapsed` (bool, default: `false`): Initial alle Items reduziert anzeigen.
- `first_open` (bool, default: `false`): Erstes Item trotz `collapsed=true` geoeffnet lassen.
- `show_toggle_all` (bool, default: `true`): Button "Alle auf / zu" in der Toolbar anzeigen.
- `open` (bool, default: `true`): Toolbar / Aktionen sind nutzbar; bei `false` wird die Bedienoberflaeche eingefroren (read-only Anzeige).
- `copy_paste` (bool, default: `true`): Pro Item ein Kopieren-Button und ein Einfuegen-Button in der Toolbar.
- `confirm_delete` (bool, default: `true`): Loeschen eines Items mit Sicherheitsabfrage.

### Sortable-Kompatibilität

- MForm lädt SortableJS nur, wenn `window.Sortable` noch nicht vorhanden ist.
- Wenn ein anderes Addon Sortable bereits global bereitstellt, wird diese Instanz verwendet.

---

## Wrapper im Repeater (Tabs, Collapse, Fieldset, Inline, Columns)

Seit **9.0.0** rendert der FlexRepeater alle gaengigen MForm-Wrapper auch innerhalb eines Repeater-Items. Damit lassen sich komplexe Item-Layouts wie im klassischen MForm-Pfad aufbauen – inkl. Tabs, Collapse-Gruppen, Fieldsets mit Legend, Form-Inline und Spalten-Grids.

| Wrapper | Methode | Markup im Repeater-Item |
|---------|---------|-------------------------|
| Tabs | `addStartGroupTab()` / `addTab()` / `addCloseTab()` / `addCloseGroupTab()` | ID-freie `nav-tabs` + `tab-content` (scoped pro Wrapper) |
| Collapse (mit Toggle-Button) | `addCollapseElement()` | `<a data-toggle="collapse">` + `.collapse[data-group-collapse-id=…]` |
| Collapse-Gruppe (Standalone-Toggle) | `addStartGroupCollapse()` / `addCloseGroupCollapse()` | `.collapse-group[data-group-accordion=0\|1]` |
| Fieldset | `addFieldsetArea($legend, …)` | `<fieldset><legend>…</legend>…</fieldset>` |
| Form-Inline | `addStartGroupInline()` / `addCloseGroupInline()` | `<div class="form-inline">` |
| Spalten-Grid | `addStartGroupColumn()` / `addColumn('col-sm-6')` / `addCloseColumn()` / `addCloseGroupColumn()` | BS3 `row` + `col-*` |
| Modal | `addModalElement()` | Bootstrap-3 Modal-Block |

### Tabs im Repeater

Tabs werden ID-frei gerendert. Die Navigation wird innerhalb des naechstgelegenen `.mform-tabs`-Wrappers gescoped und per `data-tab-item`/`data-tab-group-nav-tab-id` verknuepft. Dadurch funktionieren geklonte Repeater-Items und verschachtelte Tab-Strukturen ohne ID-Kollisionen. Aktive Tabs werden weiterhin ueber `data-group-open-tab => true` markiert.

Empfohlener Weg seit 9.1: `addTabElement()` direkt im Repeater-Item-Formular. Falls keine explizite Tab-Gruppe (`addStartGroupTab()` / `addCloseGroupTab()`) vorhanden ist, gruppiert der FlexRepeater-Renderer die Tabs automatisch.

```php
$itemForm = MForm::factory()
    ->addTabElement('Inhalt', MForm::factory()
        ->addTextField('title', ['label' => 'Titel'])
    , true, false, [
        'tab-icon' => 'fa-align-left',
        'tab-style' => 'modern',
    ])
    ->addTabElement('Meta', MForm::factory()
        ->addTextAreaField('text', ['label' => 'Text'])
    , false, false, [
        'tab-icon' => 'fa-cog',
        'tab-style' => 'modern',
    ]);

$mform->addRepeaterElement(1, $itemForm);
```

Alternative (weiterhin moeglich) ist die explizite Gruppierung mit `addStartGroupTab()` / `addTab()` / `addCloseTab()` / `addCloseGroupTab()`:

```php
$itemForm = MForm::factory()
    ->addStartGroupTab('itemTabs')
        ->addTab('itemTabs', ['data-group-open-tab' => true, 'tab-icon' => 'fa-info'])
            ->setLabel('Allgemein')
            ->addTextField('title', ['label' => 'Titel'])
        ->addCloseTab()
        ->addTab('itemTabs')
            ->setLabel('Erweitert')
            ->addTextareaField('text', ['label' => 'Text'])
        ->addCloseTab()
    ->addCloseGroupTab();

$mform->addRepeaterElement(1, $itemForm);
```

### Collapse mit `setToggleOptions()`

Selects mit `setToggleOptions()` togglen auch im Repeater zuverlaessig. Der Renderer setzt automatisch `data-toggle="collapse"` auf den Select und `data-toggle-item="…"` an den Options. Toggle-IDs sind innerhalb des Repeater-Item-Bodys gescoped (eigener `.mform`-Wrapper), darum duerfen verschiedene Items dieselbe Collapse-ID tragen, ohne sich gegenseitig zu beeinflussen.

```php
$itemForm = MForm::factory()
    ->addSelectField('mode', ['label' => 'Modus'], [1 => 'Text', 2 => 'Bild'])
        ->setToggleOptions([1 => 'modeText', 2 => 'modeImage'])
    ->addCollapseElement('', MForm::factory()
        ->addTextareaField('text', ['label' => 'Text'])
        ->show(),
        false, true, ['data-group-collapse-id' => 'modeText']
    )
    ->addCollapseElement('', MForm::factory()
        ->addMediaField('image', ['label' => 'Bild'])
        ->show(),
        false, true, ['data-group-collapse-id' => 'modeImage']
    );
```

### Fieldset mit Legend

```php
$itemForm = MForm::factory()
    ->addFieldsetArea('Kontaktdaten', MForm::factory()
        ->addTextField('name', ['label' => 'Name'])
        ->addEmailField('mail', ['label' => 'E-Mail'])
    );
```

### Spalten-Grid (Bootstrap 3)

```php
$itemForm = MForm::factory()
    ->addStartGroupColumn()
        ->addColumn('col-sm-6')
            ->addTextField('left', ['label' => 'Links'])
        ->addCloseColumn()
        ->addColumn('col-sm-6')
            ->addTextField('right', ['label' => 'Rechts'])
        ->addCloseColumn()
    ->addCloseGroupColumn();
```

> **Hinweis Bootstrap-3:** Der Repeater nutzt durchgaengig `form-horizontal` mit `col-sm-3`/`col-sm-9`-Spalten und `row` + `col-*`-Grids. Kein Flex/Grid-CSS, kein `:has()`. Die Layout-Variante des Repeaters laesst sich ueber `addRepeaterElement($id, $form, [...], ['layout' => 'horizontal'|'vertical'|'inline'])` umschalten.

---

## Media- und Link-Felder im Repeater

Der Flex-Repeater speichert alle Werte in einem JSON-Objekt. Daher gelten hier **andere Key-Konventionen als in MBlock**.

### Key-Typen

| Methode | ID-Typ | Ausgabe-Schlüssel im `$item`-Array |
|---------|--------|-------------------------------------|
| `addMediaField("bild")` | String (Pflicht!) | `$item['bild']` |
| `addLinkField("link")` | String (Pflicht!) | `$item['link']` |
| `addCustomLinkField("link")` | String (empfohlen) | `$item['link']` |
| `addMFormMediaField("bild")` | String (empfohlen) | `$item['bild']` |

> **Hinweis:** Im Repeater gibt es kein `REX_MEDIA_n`-Konzept. Die ID-Werte landen **direkt als JSON-Schlüssel** im gespeicherten Array. Numerische IDs wie `1` ergeben den Schlüssel `$item['1']`.

### Beispiel

```php
<?php
use FriendsOfRedaxo\MForm;

$itemForm = MForm::factory();
$itemForm->addTextField('title', ['label' => 'Titel']);
$itemForm->addMediaField('bild', ['label' => 'Bild']);
$itemForm->addCustomLinkField('link', ['label' => 'Link', 'intern' => 1, 'extern' => 1]);

$mform = MForm::factory();
$mform->addRepeaterElement(1, $itemForm);
echo $mform->show();
```

**Ausgabe:**

```php
<?php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$items = MFormRepeaterHelper::decode(1);

// Einheitliche Link-Normalisierung fuer Repeater + Nicht-Repeater-Formate
$items = MFormOutputHelper::normalizeRepeaterItems($items, ['link']);

foreach ($items as $item) {
    $title = rex_escape($item['title'] ?? '');
    $bildName = $item['bild'] ?? '';
    $link = $item['link_normalized']['customlink_url'] ?? '';
    $target = $item['link_normalized']['customlink_target'] ?? '';

    if ($bildName) {
        $media = rex_media::get($bildName);
        // $media->getFileName(), $media->getTitle() etc.
    }

    if ($link) {
        echo '<a href="' . rex_escape($link) . '"' . $target . '>Mehr erfahren</a>';
    }
}
```

---

## Kopieren / Einfügen (`copy_paste`)

Mit der Option `copy_paste => true` erhält jedes Item einen **Kopieren-Button** und die Toolbar einen **Einfügen-Button**.

### Verhalten

| Aktion | Beschreibung |
|--------|--------------|
| **Kopieren** (Item-Button) | Speichert alle Felder des Items als JSON in `sessionStorage`. Der `__disabled`-Status wird dabei nicht übernommen. |
| **Danach einfügen** (Item-Button) | Fügt eine Kopie direkt **nach** dem aktuellen Item ein. |
| **Paste (at end)** (Toolbar) | Fügt eine Kopie am **Ende** des Repeaters ein. |
| **Clipboard-Persistenz** | Das Clipboard bleibt nach einem Seitenreload erhalten (`sessionStorage`). Erst beim Schließen des Browser-Tabs wird es geleert. |

> Der Paste-Button ist initial unsichtbar. Er erscheint, sobald ein Item kopiert wurde – oder sofort beim Laden der Seite, falls noch ein Clipboard-Eintrag vorhanden ist.

### Beispiel

```php
<?php
use FriendsOfRedaxo\MForm;

$rowForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addSelectField('style', ['neutral' => 'Neutral', 'primary' => 'Primary'], ['label' => 'Stil'])
;

$mform = MForm::factory()
    ->addRepeaterElement(1, $rowForm, true, true, [
        'label'       => 'Zeilen',
        'btn_text'    => 'Zeile hinzufügen',
        'copy_paste'  => true,  // ← Kopieren/Einfügen aktivieren
        'collapsed'   => true,
        'first_open'  => true,
    ])
;

echo $mform->show();
```

### Vollständige Optionsübersicht

| Option | Typ | Standard | Beschreibung |
|--------|-----|---------|--------------|
| `label` | string | `''` | Bezeichnung über dem Repeater |
| `btn_text` | string | `'Add'` | Text des Hinzufügen-Buttons |
| `collapsed` | bool | `false` | Items initial zugeklappt |
| `first_open` | bool | `false` | Erstes Item trotz `collapsed` offen |
| `show_toggle_all` | bool | `true` | „Alle auf/zu"-Button in Toolbar |
| `min` | int | `0` | Mindestanzahl Items |
| `max` | int | `0` | Maximalanzahl Items (0 = unbegrenzt) |
| `confirm_delete` | bool | `true` | Loeschen mit Bestaetigung |
| `confirm_delete_msg` | string | `''` | Text der Bestätigungsmeldung |
| `copy_paste` | bool | `true` | Kopieren/Einfügen-Funktion aktivieren |
| `open` | bool | `true` | Toolbar / Bedienung aktiv (`false` = read-only) |

---

## MForm Flex-Repeater vs. MBlock

| Merkmal | MForm Flex-Repeater | MBlock |
|---|---|---|
| **Architektur** | PHP rendert Templates, JS klont nur | JS generiert und verwaltet HTML selbst |
| **JS-Codebasis** | ~1.500 Zeilen | ~3.400 Zeilen |
| **jQuery-Abhängigkeit** | Nein – Vanilla JS | Ja – jQuery erforderlich |
| **Template-System** | HTML `<template>`-Element (nativer Browser-Standard) | `data-mblock-plain-sortitem` – HTML als Data-Attribut serialisiert |
| **Verschachtelung (Nesting)** | ✅ Stabil auf mind. 2 Ebenen, funktioniert transparent | ⚠️ Bekannt instabil – ID-Kollisionen, Sortable-Konflikte, Editor-Probleme |
| **TinyMCE / Editoren** | Stabile Initialisierung in Repeatern und verschachtelten Kontexten | Erfordert manuelles Destroy/Reinit beim Sortieren; fehleranfällig |
| **Kopieren / Einfügen** | ✅ `copy_paste => true`, sessionStorage-basiert | ✅ vorhanden, ebenfalls sessionStorage |
| **Aktiv/Inaktiv-Toggle** | ✅ pro Item, mit visueller Statusanzeige | ✅ vorhanden |
| **Drag & Drop** | SortableJS (im Addon mitgeliefert) | Eigene Sortable-Implementierung (im Addon mitgeliefert) |
| **Wartbarkeit** | Hoch – PHP-Rendering klar getrennt von JS | Niedrig – JS muss HTML-Struktur kennen und selbst erzeugen |
| **Block-Typen** | Eine Struktur pro Repeater-Instanz | Mehrere Block-Typen pro Instanz möglich |
| **MBlock-Migration** | ✅ Migrationsleitfaden vorhanden | – |
| **Template-API** | ✅ `MForm::registerTemplate()` für projektweite Vorlagen | – |
| **Frontend-Ausgabe** | `MFormRepeaterHelper` mit `decode()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()` | `MBlock::filterByField()`, `sortByField()`, `groupByField()`, `limitItems()` – MForm hat diese API von MBlock übernommen und in `MFormRepeaterHelper` integriert |

### Wann MForm Flex-Repeater bevorzugen

- Neue Module – der Flex-Repeater ist der empfohlene Standard
- Verschachtelte Repeater (Repeater im Repeater)
- TinyMCE oder MarkdownEditor in Repeater-Zeilen
- Kein jQuery im Projekt gewünscht
- Wartbarkeit und langfristige Pflege wichtig

### Wann MBlock behalten

- Bestehende Module mit mehreren **verschiedenen Block-Typen** pro Instanz (das einzige MBlock-Feature ohne direktes Äquivalent im Flex-Repeater)
- Wenn keine Migration gewünscht ist

> Migration von bestehenden MBlock-Modulen: siehe [08_mblock_migration.md](08_mblock_migration.md).

