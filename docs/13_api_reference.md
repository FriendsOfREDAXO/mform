# MForm API-Referenz

Vollständige Referenz aller öffentlichen Klassen, Methoden und Felder von MForm v9.

---

## Inhaltsverzeichnis

- [Klasse `MForm`](#klasse-mform)
- [Felder hinzufügen (`addXxx`)](#felder-hinzufügen)
  - [Layout & Struktur](#layout--struktur)
  - [Text & Eingabe](#text--eingabe)
  - [Auswahl-Felder](#auswahl-felder)
  - [Radio-Felder (visuell)](#radio-felder-visuell)
  - [Medien & Links](#medien--links)
  - [Repeater](#repeater)
  - [Sonstiges](#sonstiges)
- [Optionen setzen (`setXxx`)](#optionen-setzen)
- [Klasse `MFormRepeaterHelper`](#klasse-mformrepeaterhelper)
- [Klasse `MFormOutputHelper`](#klasse-mformoutputhelper)
- [Klasse `MFormModuleHelper`](#klasse-mformmodulehelper)
- [Template-System](#template-system)
- [DTO `MFormItem`](#dto-mformitem)
- [Felder-Typ-Referenz](#felder-typ-referenz)

---

## Klasse `MForm`

**Namespace:** `FriendsOfRedaxo`  
**Datei:** `lib/MForm.php`  
**Erweitert:** `MFormElements`

Einstiegspunkt für alle Formular-Definitionen. Immer via `MForm::factory()` instanziieren.

### Statische Methoden

```php
MForm::factory(bool $debug = false): MForm
```
Erstellt eine neue MForm-Instanz.

---

```php
MForm::fromTemplate(string $key, array $context = [], bool $debug = false): MForm
```
Erstellt eine MForm aus einem registrierten Template.

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `$key` | `string` | Template-Schlüssel (vorher registriert) |
| `$context` | `array` | Kontextdaten für das Template |
| `$debug` | `bool` | Debug-Modus aktivieren |

---

```php
MForm::registerTemplate(string $key, string $templateClass): void
```
Registriert eine Template-Klasse für einen Key. `$templateClass` muss `TemplateInterface` implementieren.

---

```php
MForm::unregisterTemplate(string $key): void
```
Entfernt einen registrierten Template-Key.

---

```php
MForm::hasTemplate(string $key): bool
```
Prüft, ob ein Template-Key registriert ist.

---

```php
MForm::useCustomLinkForClassicWidgets(bool $enable = true): void
```
Schaltet custom_link-Rendering für `addMediaField()` / `addLinkField()` ein/aus.  
Standard: `false` (klassisches Core-Widget, Rückwärtskompatibilität).

---

```php
MForm::isUsingCustomLinkForClassicWidgets(): bool
```
Gibt zurück, ob custom_link-Rendering für klassische Widgets aktiv ist.

---

### Instanz-Methoden

```php
$mform->show(): string
```
Parst alle definierten Felder und gibt das fertige HTML zurück.

---

```php
$mform->applyTemplate(string $key, array $context = []): self
```
Wendet ein registriertes Template auf die aktuelle Form an.

---

```php
$mform->setTheme(string $theme): self
```
Setzt ein alternatives Theme für das Fragment-Rendering.

---

```php
$mform->setDebug(bool $debug): self
```
Aktiviert/deaktiviert den Debug-Modus.

---

```php
$mform->setShowWrapper(bool $showWrapper): self
```
Steuert, ob der äußere Wrapper-Container gerendert wird.

---

## Felder hinzufügen

Alle `addXxx()`-Methoden geben `$this` (MForm-Instanz) zurück und sind vollständig chainbar.

### Layout & Struktur

```php
addHtml(?string $html = null): MForm
```
Fügt beliebiges HTML-Fragment ein.

---

```php
addHeadline(?string $value = null, ?array $attributes = null): MForm
```
Überschrift (h3-Element).

---

```php
addDescription(?string $value = null): MForm
```
Beschreibungstext (p-Element).

---

```php
addAlert(string $key, ?string $value = null): MForm
```
Bootstrap-Alert. `$key`: `info` | `warning` | `danger` | `success`

---

```php
addAlertInfo(?string $value = null): MForm
addAlertWarning(?string $value = null): MForm
addAlertDanger(?string $value = null): MForm
addAlertError(?string $value = null): MForm   // Alias für addAlertDanger
addAlertSuccess(?string $value = null): MForm
```
Kurzformen für spezifische Alert-Typen.

---

```php
addForm(callable|MForm|string|null $form = null, bool $parse = false, bool $debug = false, bool $showWrapper = false): MForm
```
Bettet eine Sub-Form ein (MForm-Objekt, Callable oder HTML-String).

---

```php
addFieldsetArea(?string $legend = null, mixed $form = null, array $attributes = [], bool $parse = false, bool $showWrapper = false): MForm
```
Gruppiert Felder in einem `<fieldset>` mit optionalem `<legend>`.

---

```php
addColumnElement(int $col, mixed $form = null, array $attributes = [], bool $parse = false, bool $showWrapper = false): MForm
```
Bootstrap-Grid-Spalte. `$col`: Anzahl der Bootstrap-Spalten (1–12).  
Erzeugt `col-sm-{n}` wenn kein eigenes `col-*` in `$attributes['class']` gesetzt.

---

```php
addInlineElement(string $label = '', mixed $form = null, array $attributes = [], bool $parse = false, bool $showWrapper = false): MForm
```
Inline-Layout-Container mit Label.

---

```php
addTabElement(string $label = '', mixed $form = null, bool $openTab = false, bool $pullNaviItemRight = false, array $attributes = [], bool $parse = false, bool $showWrapper = false): MForm
```
Tab-Panel. Mehrere Tabs werden automatisch zu einer Tab-Leiste zusammengefasst.
Funktioniert auch innerhalb von FlexRepeater-, Fieldset-, Collapse- und Modal-Kontexten.

| Parameter | Typ | Standard | Beschreibung |
|-----------|-----|----------|--------------|
| `$label` | `string` | `''` | Tab-Bezeichnung |
| `$openTab` | `bool` | `false` | Tab initial geöffnet |
| `$pullNaviItemRight` | `bool` | `false` | Tab-Item nach rechts schieben |

Optionale `$attributes` fuer Tabs:

- `tab-icon` (z. B. `fa-cog`): Icon im Tab-Label.
- `tab-style => 'modern'` oder `data-group-tab-style => 'modern'`: modernisierte Tab-Optik.
- `tab-layout => 'vertical'` oder `data-group-tab-layout => 'vertical'`: vertikale Navigation links neben dem Tab-Content.

Visual Form Builder (9.1): Fuer Tab-Elemente koennen `tab-icon`, `tab-style` und `tab-layout` direkt in den Eigenschaften gesetzt werden.

---

```php
addCollapseElement(string $label = '', callable|MForm|string|null $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = [], bool $accordion = false, bool $parse = false, bool $showWrapper = false): MForm
```
Aufklappbares Panel (Bootstrap Collapse).

---

```php
addAccordionElement(string $label = '', callable|MForm|string|null $form = null, bool $openCollapse = false, bool $hideToggleLinks = false, array $attributes = []): MForm
```
Accordion-Gruppe (mehrere Collapse-Elemente, nur eines gleichzeitig offen).

---

```php
addModalElement(string $label = '', callable|MForm|string|null $form = null, string $btnClass = 'btn-default', string $align = 'left', array $attributes = [], bool $parse = false, bool $showWrapper = false): MForm
```
Modal-Dialog. Felder innerhalb des Modals werden zusammen mit dem Haupt-Formular gespeichert.

| Parameter | Typ | Standard | Beschreibung |
|-----------|-----|----------|--------------|
| `$label` | `string` | `''` | Button-Label und Modal-Titel |
| `$btnClass` | `string` | `'btn-default'` | CSS-Klasse(n) für den Trigger-Button |
| `$align` | `string` | `'left'` | Ausrichtung: `'left'` \| `'right'` |

---

```php
addConditionalFieldsetArea(
    float|int|string $sourceField,
    string $operator = '=',
    string $compareValue = '',
    string $legend = '',
    callable|MForm|string|null $form = null,
    array $attributes = [],
    bool $parse = false,
    bool $showWrapper = false,
    string $action = 'show'
): MForm
```
Fieldset, das per JavaScript ein- oder ausgeblendet wird, basierend auf dem Wert eines anderen Feldes.

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `$sourceField` | `float\|int\|string` | Feld-ID des steuernden Feldes |
| `$operator` | `string` | Vergleichsoperator: `=`, `!=`, `>`, `<`, `>=`, `<=` |
| `$compareValue` | `string` | Vergleichswert |
| `$action` | `string` | `'show'` oder `'hide'` |

---

### Text & Eingabe

```php
addTextField(float|int|string $id, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Einzeiliges Textfeld (`<input type="text">`).

---

```php
addTextAreaField(float|int|string $id, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Mehrzeiliges Textfeld (`<textarea>`).

---

```php
addTextReadOnlyField(float|int|string $id, ?string $value = null, ?array $attributes = null): MForm
```
Schreibgeschütztes Textfeld.

---

```php
addTextAreaReadOnlyField(float|int|string $id, ?string $value = null, ?array $attributes = null): MForm
```
Schreibgeschützte Textarea.

---

```php
addHiddenField(float|int|string $id, ?string $value = null, ?array $attributes = null): MForm
```
Verstecktes Feld (`<input type="hidden">`).

---

```php
addInputField(string $typ, float|int|string $id, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Generisches Input-Feld. `$typ` entspricht dem HTML-`type`-Attribut.

---

### Auswahl-Felder

```php
addSelectField(float|int|string $id, ?array $options = null, ?array $attributes = null, int $size = 1, ?string $defaultValue = null): MForm
```
Select-Dropdown.

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `$options` | `array\|null` | Assoziatives Array `[value => label]` |
| `$size` | `int` | Anzahl sichtbarer Einträge (>1 = Listbox) |

---

```php
addMultiSelectField(float|int|string $id, ?array $options = null, ?array $attributes = null, int $size = 3, ?string $defaultValue = null): MForm
```
Mehrfach-Auswahl-Listbox.

---

```php
addCheckboxField(float|int|string $id, ?array $options = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Checkbox-Gruppe.

---

```php
addToggleCheckboxField(float|int|string $id, ?array $options = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Toggle-Checkbox mit visueller Umschalter-Darstellung.

---

```php
addRadioField(float|int|string $id, ?array $options = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Radio-Button-Gruppe.

---

```php
addCheckboxGroupField(float|int|string $id, ?array $options = null, ?array $attributes = null): MForm
```
Visuell gestaltete Checkbox-Gruppe. Gespeicherter Wert: kommaseparierter String der ausgewählten Keys.

```php
// Ausgabe: $item['tags'] → 'news,event'
$selected = array_filter(explode(',', $item['tags'] ?? ''));
```

---

### Radio-Felder (visuell)

```php
addRadioImgField(float|int|string $id, ?array $options = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Radio-Gruppe mit Bild-Vorschau. Options-Format:

```php
$options[$value] = ['img' => '/path/to/img.svg', 'label' => 'Variante 1'];
// oder mit SVG-Icon-Set:
$options[$value] = ['svgIconSet' => '<svg>...</svg>', 'label' => 'Variante 2'];
// oder mit Layout-Config (LayoutPreviewHelper):
$options[$value] = ['config' => [...], 'label' => 'Variante 3'];
```

---

```php
addRadioIconField(float|int|string $id, ?array $options = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Radio-Gruppe mit Icon-Vorschau (Font Awesome etc.).

```php
$options[$value] = ['icon' => 'fa fa-home', 'label' => 'Startseite'];
```

---

```php
addRadioColorField(float|int|string $id, ?array $options = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Radio-Gruppe mit Farb-Swatches.

```php
$options[$value] = ['color' => '#ff0000', 'label' => 'Rot'];
$options['none'] = ['color' => 'transparent', 'label' => 'Kein'];
```

---

```php
addColorSwatchField(float|int|string $id, ?array $swatches = null, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Farb-Picker mit Text-Input und vordefinierten Farb-Swatches. Gespeicherter Wert: Hex-Code oder CSS-Klasse.

```php
$mform->addColorSwatchField('color', [
    '#ffffff' => 'Weiß',
    '#000000' => 'Schwarz',
    '.bg-primary' => ['label' => 'Primärfarbe', 'preview' => '#2f77bc'],
], ['label' => 'Farbe']);
```

---

### Medien & Links

```php
addMediaField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
Medienpool-Datei-Picker (einzelne Datei).

---

```php
addMedialistField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
Medienpool-Dateiliste (mehrere Dateien).

---

```php
addImagelistField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
Medienpool-Bilderliste.

---

```php
addLinkField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
REDAXO-internen Artikel-Link-Picker (REX_LINK Widget).

---

```php
addLinklistField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
REDAXO-Artikel-Link-Liste.

---

```php
addCustomLinkField(float|int|string $id, ?array $attributes = null, ?string $defaultValue = null): MForm
```
Universeller Link-Picker (intern/extern/Medien/mailto/tel).

Unterstützte `$attributes` (data-Attribute):

| Attribut | Werte | Beschreibung |
|----------|-------|--------------|
| `data-intern` | `enable`\|`disable` | Interne Links erlauben |
| `data-extern` | `enable`\|`disable` | Externe Links erlauben |
| `data-media` | `enable`\|`disable` | Medienpool-Links erlauben |
| `data-mailto` | `enable`\|`disable` | mailto-Links erlauben |
| `data-tel` | `enable`\|`disable` | tel-Links erlauben |
| `data-extern-link-prefix` | `string` | Prefix für externe Links |
| `data-link-category` | `int` | Start-Kategorie für interne Links |
| `data-media-category` | `int` | Start-Kategorie für Medien |
| `data-media-type` | `string` | Erlaubte Dateiendungen (kommasepariert) |
| `ylink` | `array` | YForm-Tabellen-Links |

---

```php
addCustomLinkMultipleField(float|int|string $id, ?array $attributes = null): MForm
```
Mehrfach-Link-Widget. Daten als JSON-Array in einem Hidden-Input.

---

```php
addMFormLinkField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
MForm-eigener Link-Wrapper auf Basis von custom_link (interne Links fokussiert).

---

```php
addMFormMediaField(float|int|string $id, ?array $parameter = null, mixed $catId = null, ?array $attributes = null): MForm
```
MForm-eigener Media-Wrapper auf Basis von custom_link (Datei-Fokus, kein Reindex-Problem in MBlock).

---

### Repeater

```php
addRepeaterElement(float|int|string $id, MForm $form, bool $open = true, bool $confirmDelete = true, array $attributes = [], bool $debug = false, bool $showWrapper = false): MForm
```
Flex-Repeater (Legacy-API, intern identisch mit `addFlexRepeaterElement`).

| Parameter | Typ | Standard | Beschreibung |
|-----------|-----|----------|--------------|
| `$id` | `float\|int\|string` | — | REX_VALUE Slot (1–20) |
| `$form` | `MForm` | — | Sub-Form für ein Repeater-Item |
| `$open` | `bool` | `true` | Erstes Item initial geöffnet |
| `$confirmDelete` | `bool` | `true` | Lösch-Bestätigung anzeigen |
| `$attributes` | `array` | `[]` | Zusätzliche Optionen (siehe unten) |

Unterstützte `$attributes` (Repeater-Optionen):

| Schlüssel | Typ | Beschreibung |
|-----------|-----|--------------|
| `min` | `int` | Mindestanzahl Items |
| `max` | `int` | Maximalanzahl Items |
| `default_count` | `int` | Anfangs-Items bei leerem Wert |
| `btn_text` | `string` | Text des "Item hinzufügen"-Buttons |
| `btn_class` | `string` | CSS-Klasse des Add-Buttons |
| `confirm_delete_msg` | `string` | Angepasste Lösch-Bestätigungsmeldung |

---

```php
addFlexRepeaterElement(float|int|string $id, MForm $form, array $options = [], bool $debug = false, bool $showWrapper = false): MForm
```
Flex-Repeater (explizite API). Speichert Daten als JSON in einem REX_VALUE-Slot.

---

### Sonstiges

```php
addElement(string $type, float|int|string|null $id = null, ?string $value = null, ?array $attributes = null, ?array $options = null, ?array $parameter = null, ?int $catId = null, ?string $defaultValue = null): MForm
```
Universelle Low-Level-Methode für alle Feldtypen. Für benutzerdefinierte Felder.

---

```php
addInputs(float|int|string|null $id, string $filename, array $inputsConfig = []): ?MForm
```
Lädt eine externe Input-Klasse aus `mform/inputs/` oder `mfragment/inputs/`.

---

## Optionen setzen

Alle `setXxx()`-Methoden wirken auf das zuletzt hinzugefügte Feld und geben `$this` zurück.

```php
setLabel(string $label): MForm
```
Setzt das Label des aktuellen Feldes.

---

```php
setPlaceholder(string $placeholder): MForm
```
Setzt den Platzhaltertext.

---

```php
setFull(): MForm
```
Setzt das Feld auf volle Breite (kein Label-Spalten-Layout).

---

```php
setFormItemColClass(string $class): MForm
```
Überschreibt die Bootstrap-Spaltenklasse des Feld-Containers.

---

```php
setLabelColClass(string $class): MForm
```
Überschreibt die Bootstrap-Spaltenklasse des Label-Containers.

---

```php
setAttribute(string $name, mixed $value): MForm
```
Setzt ein einzelnes HTML-Attribut.

---

```php
setAttributes(array $attributes): MForm
```
Setzt mehrere HTML-Attribute auf einmal.

---

```php
setDefaultValue(string $value): MForm
```
Setzt den Standardwert (wird verwendet, wenn kein gespeicherter Wert vorhanden).

---

```php
setOptions(array $options): MForm
```
Setzt die Auswahl-Optionen (für Select, Checkbox, Radio). Format: `[value => label]`.

---

```php
setOption(mixed $key, mixed $value): MForm
```
Fügt eine einzelne Auswahl-Option hinzu.

---

```php
setToggleOptions(array $options): MForm
```
Verknüpft Auswahl-Optionen mit Elementen, die beim Wählen ein-/ausgeblendet werden.  
Format: `[optionValue => targetFieldId]`

---

```php
setDisableOptions(array $keys): MForm
```
Deaktiviert bestimmte Optionen (rendered als `disabled`).

---

```php
setDisableOption(mixed $key): MForm
```
Deaktiviert eine einzelne Option.

---

```php
setSqlOptions(string $query): MForm
```
Befüllt Optionen aus einer SQL-Abfrage.

---

```php
setMultiple(): MForm
```
Aktiviert Mehrfachauswahl (`multiple`-Attribut).

---

```php
setSize(int $size): MForm
```
Setzt die sichtbare Anzahl von Einträgen.

---

```php
setCategory(int|null $catId): MForm
```
Setzt die Start-Kategorie für Medienpool/Link-Felder.

---

```php
setParameters(array $parameter): MForm
```
Setzt Widget-spezifische Parameter (z. B. für Medialist).

---

```php
setParameter(string $name, mixed $value): MForm
```
Setzt einen einzelnen Parameter.

---

```php
setTooltipInfo(?string $value = null, string $icon = ''): MForm
```
Zeigt ein Info-Tooltip-Icon neben dem Label.

- Default ohne Icon-Parameter: `fa-info-circle`
- Eigenes Icon weiterhin möglich, z. B. `fa-question-circle`

---

```php
setTabIcon(string $icon): MForm
```
Setzt ein Icon für Tab-Elemente (Font Awesome Klassen).

---

```php
setCollapseInfo(?string $value = null, string $icon = ''): MForm
```
Fügt einem Collapse-Element eine Info-Nachricht hinzu.

---

```php
pullRight(): MForm
```
Verschiebt ein Tab-Navi-Element nach rechts.

---

```php
getItems(): array
```
Gibt das interne Items-Array zurück (nützlich für Templates und Sub-Forms).

---

```php
setItems(array $items): MForm
```
Setzt das interne Items-Array direkt.

---

```php
getResult(): array
```
Gibt die geladenen REX_VALUE-Ergebnisse zurück.

---

## Klasse `MFormRepeaterHelper`

**Namespace:** `FriendsOfRedaxo\MForm\Repeater`  
**Datei:** `lib/MForm/Repeater/MFormRepeaterHelper.php`

Statische Hilfsmethoden für die Verarbeitung von Repeater-Daten im Modul-Output.

### Methoden

```php
MFormRepeaterHelper::decode(int|string $source): array
```
**Hauptmethode für die Ausgabe.** Dekodiert Repeater-Daten und gibt bereinigte, aktive Items zurück. Filtert automatisch deaktivierte Items heraus.

```php
// Modul-Output:
$rows = MFormRepeaterHelper::decode(1);
foreach ($rows as $row) {
    echo $row['title'];
}
```

---

```php
MFormRepeaterHelper::filterEnabledItems(array $items): array
```
Filtert deaktivierte Items aus einem bereits dekodiertem Array. (In `decode()` bereits enthalten.)

---

```php
MFormRepeaterHelper::isItemEnabled(array $item): bool
```
Prüft, ob ein einzelnes Item aktiv (nicht deaktiviert) ist.

---

```php
MFormRepeaterHelper::prepareItemsForOutput(array $items): array
```
Bereinigt interne Meta-Keys (`__disabled`) und verarbeitet verschachtelte Repeater rekursiv.

---

```php
MFormRepeaterHelper::filterByField(array $items, string $field, mixed $value, bool $strict = false): array
```
Filtert Items nach einem Feldwert.

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `$items` | `array` | Dekodiertes Items-Array |
| `$field` | `string` | Feldname |
| `$value` | `mixed` | Gesuchter Wert |
| `$strict` | `bool` | Strikt (`===`) statt locker (`==`) vergleichen |

```php
$activeItems = MFormRepeaterHelper::filterByField($rows, 'category', 'news');
```

---

```php
MFormRepeaterHelper::sortByField(array $items, string $field, string $direction = 'asc'): array
```
Sortiert Items nach einem Feldwert (numerisch oder alphabetisch).

```php
$sorted = MFormRepeaterHelper::sortByField($rows, 'date', 'desc');
```

---

```php
MFormRepeaterHelper::groupByField(array $items, string $field): array
```
Gruppiert Items nach einem Feldwert. Gibt `array<string, array<int, array>>` zurück.

```php
$grouped = MFormRepeaterHelper::groupByField($rows, 'category');
foreach ($grouped as $category => $categoryItems) { ... }
```

---

```php
MFormRepeaterHelper::limitItems(array $items, int $limit, int $offset = 0): array
```
Begrenzt die Anzahl der Items (für Pagination).

```php
$page1 = MFormRepeaterHelper::limitItems($rows, 10, 0);
$page2 = MFormRepeaterHelper::limitItems($rows, 10, 10);
```

---

## Klasse `MFormOutputHelper`

**Namespace:** `FriendsOfRedaxo\MForm\Utils`  
**Datei:** `lib/MForm/Utils/MFormOutputHelper.php`

Statische Helfer für die Aufbereitung von Link- und Mediendaten im Modul-Output.

### Methoden

```php
MFormOutputHelper::createLinkData(mixed $input, array $options = []): array
```
Alias für `normalizeLinkData()`. Unified Entry-Point für alle Link-Typen.

---

```php
MFormOutputHelper::normalizeLinkData(mixed $input, array $options = []): array
```
Normalisiert beliebige Link-Eingaben in eine einheitliche Ausgabe-Struktur.

**Unterstützte Inputs:**
- String-Linkwert (`redaxo://10`, `https://...`, `mailto:...`)
- Array mit `link`-Key
- Repeater-ähnliches Array mit `id`/`name`
- Bereits aufbereitete Arrays mit `customlink_url`

**Options:**

| Schlüssel | Typ | Standard | Beschreibung |
|-----------|-----|----------|--------------|
| `extern_blank` | `bool` | `true` | Externe Links in neuem Tab öffnen |
| `mode` | `string` | `'frontend'` | `frontend` \| `raw` \| `strict` |

**Rückgabe-Array:**

| Schlüssel | Beschreibung |
|-----------|--------------|
| `customlink_url` | Fertige URL |
| `customlink_text` | Link-Text (aus Artikelname, Medientitel etc.) |
| `customlink_target` | Target-Attribut (z. B. `target="_blank" rel="noopener noreferrer"`) |
| `customlink_class` | CSS-Klasse (`intern`, `external`, `media`, `mail`, `tel`) |
| `type` | Link-Typ: `internal` \| `external` \| `media` \| `email` \| `telephone` \| `undefined` |
| `article_id` | Artikel-ID (nur bei internen Links) |
| `filename` | Dateiname (nur bei Medien-Links) |
| `extension` | Dateiendung (nur bei Medien-Links) |
| `metadata` | Zusätzliche Metadaten (Artikel-Name, Medien-Dimensionen etc.) |

```php
$link = MFormOutputHelper::normalizeLinkData('REX_LINK[id=1 output="url"]');
echo '<a href="' . $link['customlink_url'] . '"' . $link['customlink_target'] . '>' . $link['customlink_text'] . '</a>';
```

---

```php
MFormOutputHelper::normalizeRepeaterItems(array $items, array $linkFields, array $options = []): array
```
Normalisiert Link-Felder in Repeater-Items. Fügt standardmäßig `<field>_normalized`-Keys hinzu.

| Parameter | Typ | Beschreibung |
|-----------|-----|--------------|
| `$items` | `array` | Repeater-Items (aus `MFormRepeaterHelper::decode()`) |
| `$linkFields` | `array` | Feldnamen der zu normalisierenden Link-Felder |
| `$options['replace']` | `bool` | `true`: Original-Feld überschreiben (default: `false`) |

```php
$rows = MFormRepeaterHelper::decode(1);
$rows = MFormOutputHelper::normalizeRepeaterItems($rows, ['link', 'image']);
// Jetzt: $row['link_normalized']['customlink_url']
```

---

```php
MFormOutputHelper::prepareCustomLink(array $item, bool $externBlank = true): array
```
Reichert ein Link-Array mit Metadaten an (Artikelname, Medien-Dimensionen etc.).

---

```php
MFormOutputHelper::getCustomLinkUrl(array|string $item, bool $externBlank = true): string
```
Gibt nur die URL für einen Link-Wert zurück.

---

```php
MFormOutputHelper::getCustomUrl(mixed $value = null, ?string $lang = null): string
```
Konvertiert REDAXO-Links (`redaxo://`, `rex://`, numerische IDs) in frontend-URLs.

---

```php
MFormOutputHelper::isFirstSlice(mixed $sliceId): bool
```
Prüft, ob ein Modul-Slice der erste Slice des aktuellen Artikels ist.

---

## Klasse `MFormModuleHelper`

**Namespace:** `FriendsOfRedaxo\MForm\Utils`  
**Datei:** `lib/MForm/Utils/MFormModuleHelper.php`

Hilfsmethoden für Modulkonfigurationen und Backend-Vorschauen.

```php
MFormModuleHelper::mergeInputConfig(array $defaultConfig = [], array $config = [], int $maxMergeDepth = PHP_INT_MAX, int $currentDepth = 0): array
```
Mergt eine benutzerdefinierte Konfiguration in eine Standard-Konfiguration. Überschreibt vorhandene Keys, fügt neue hinzu.

---

```php
MFormModuleHelper::mergeOutputConfig(array $defaultConfig = [], array $config = []): array
```
Mergt Output-Konfigurationen. Respektiert den Spezialwert `'mfragment_default'` als "nicht überschreiben".

---

```php
MFormModuleHelper::addBackendInfoMsg(string $message): void
```
Fügt eine HTML-Nachricht zur Backend-Info-Box hinzu.

---

```php
MFormModuleHelper::addBackendInfoImgMsg(mixed $image, string $message = '', string $mediaType = 'rex_mediapool_detail'): void
```
Fügt eine Nachricht mit Vorschau-Bild zur Backend-Info-Box hinzu.

---

```php
MFormModuleHelper::addBackendInfoImgList(mixed $images, string $message = '', string $mediaType = 'rex_mediapool_detail'): void
```
Fügt eine Nachricht mit mehreren Vorschau-Bildern zur Backend-Info-Box hinzu.

---

```php
MFormModuleHelper::exchangeBackendInfo(string $headline = 'Settings', string $viewType = 'content'): string
```
Gibt die gesammelten Backend-Info-Nachrichten als HTML-Box zurück und leert den internen Puffer.  
Nur im Backend aktiv.

```php
// Modul-Input:
MFormModuleHelper::addBackendInfoMsg('Konfigurierter Stil: ' . $style);
echo MFormModuleHelper::exchangeBackendInfo('Vorschau');
echo $mform->show();
```

---

## Template-System

### Interface `TemplateInterface`

**Namespace:** `FriendsOfRedaxo\MFormTemplate`  
**Datei:** `lib/MFormTemplate/TemplateInterface.php`

```php
interface TemplateInterface
{
    public function apply(MForm $form, array $context = []): MForm;
}
```

Eigene Template-Klassen müssen dieses Interface implementieren.

### Eigenes Template erstellen

```php
use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MFormTemplate\TemplateInterface;

class MyHeroTemplate implements TemplateInterface
{
    public function apply(MForm $form, array $context = []): MForm
    {
        $form->addTextField(1, ['label' => 'Überschrift']);
        $form->addTextAreaField(2, ['label' => 'Text']);
        if (!empty($context['with_image'])) {
            $form->addMediaField(3, [], null, ['label' => 'Bild']);
        }
        return $form;
    }
}

// Registrieren (z. B. in boot.php):
MForm::registerTemplate('hero', MyHeroTemplate::class);

// Verwenden im Modul-Input:
echo MForm::fromTemplate('hero', ['with_image' => true])->show();
// oder:
echo MForm::factory()->applyTemplate('hero')->show();
```

---

## DTO `MFormItem`

**Namespace:** `FriendsOfRedaxo\MForm\DTO`  
**Datei:** `lib/MForm/DTO/MFormItem.php`

Internes Datenobjekt für jedes Formularfeld. Wird normalerweise nicht direkt verwendet.

Wichtige Methoden (intern/für Template-Entwicklung):

| Methode | Beschreibung |
|---------|--------------|
| `getType(): string` | Feld-Typ |
| `getId(): int` | Interne ID (Position im Items-Array) |
| `getVarId(): string\|array` | REX_VALUE-ID des Feldes |
| `getAttributes(): array` | HTML-Attribute und MForm-Attribute |
| `getOptions(): array` | Auswahl-Optionen |
| `getDefaultValue(): string` | Standardwert |
| `addAttribute(string $key, mixed $value): self` | Attribut hinzufügen |
| `setClass(string $class): self` | CSS-Klasse setzen |
| `getClass(): string` | CSS-Klasse auslesen |

---

## Felder-Typ-Referenz

Vollständige Liste aller intern verwendeten Feld-Typen (für `addElement()`):

| Typ | Methode | Beschreibung |
|-----|---------|--------------|
| `text` | `addTextField()` | Einzeiliger Text-Input |
| `textarea` | `addTextAreaField()` | Mehrzeiliger Text-Input |
| `text-readonly` | `addTextReadOnlyField()` | Schreibgeschützter Text |
| `textarea-readonly` | `addTextAreaReadOnlyField()` | Schreibgeschützte Textarea |
| `hidden` | `addHiddenField()` | Verstecktes Feld |
| `select` | `addSelectField()` | Dropdown-Auswahl |
| `multiselect` | `addMultiSelectField()` | Mehrfach-Auswahl |
| `checkbox` | `addCheckboxField()` | Checkbox-Gruppe |
| `checkbox-group` | `addCheckboxGroupField()` | Visuell gestaltete Checkbox-Gruppe |
| `radio` | `addRadioField()` | Radio-Buttons |
| `color-swatch` | `addColorSwatchField()` | Farb-Swatch-Picker |
| `link` | `addLinkField()` | REDAXO-internen Link-Picker |
| `linklist` | `addLinklistField()` | REDAXO-Link-Liste |
| `media` | `addMediaField()` | Medienpool-Picker |
| `medialist` | `addMedialistField()` | Medienpool-Liste |
| `imglist` | `addImagelistField()` | Medienpool-Bilderliste |
| `custom-link` | `addCustomLinkField()` | Universeller Link-Picker |
| `custom-link-multi` | `addCustomLinkMultipleField()` | Mehrfach-Link-Widget |
| `mform-link` | `addMFormLinkField()` | MForm-Link-Wrapper |
| `mform-media` | `addMFormMediaField()` | MForm-Medien-Wrapper |
| `html` | `addHtml()` | HTML-Fragment |
| `headline` | `addHeadline()` | Überschrift |
| `description` | `addDescription()` | Beschreibungstext |
| `alert` | `addAlert()` | Bootstrap-Alert |
| `fieldset` | `addFieldsetArea()` | Fieldset-Gruppe |
| `column` | `addColumnElement()` | Bootstrap-Spalte |
| `inline` | `addInlineElement()` | Inline-Container |
| `tab` | `addTabElement()` | Tab-Panel |
| `collapse` | `addCollapseElement()` | Collapse-Panel |
| `repeater` | `addFlexRepeaterElement()` | Flex-Repeater |

---

## Vollständiges Verwendungsbeispiel

```php
// Modul-Input

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\Utils\MFormModuleHelper;

$config = MFormModuleHelper::mergeInputConfig([
    'show_image' => true,
    'max_items' => 10,
], rex_var::toArray('REX_VALUE[id=20]'));

$mform = MForm::factory();

// Tabs
$mform->addTabElement('Inhalt', function() use ($config) {
    $form = MForm::factory();
    $form->addTextField(1, ['label' => 'Überschrift']);
    $form->addTextAreaField(2, ['label' => 'Text', 'class' => 'cke5-editor']);
    if ($config['show_image']) {
        $form->addMediaField(3, [], null, ['label' => 'Bild']);
    }
    return $form;
}, true);

$mform->addTabElement('Einstellungen', function() use ($config) {
    $form = MForm::factory();
    $form->addSelectField(4, [
        'card' => 'Karte',
        'list' => 'Liste',
        'grid' => 'Gitter',
    ], ['label' => 'Layout']);

    $repeaterForm = MForm::factory();
    $repeaterForm->addTextField('1.title', ['label' => 'Titel']);
    $repeaterForm->addCustomLinkField('1.link', ['label' => 'Link']);
    $form->addRepeaterElement(5, $repeaterForm, true, true, [
        'max' => $config['max_items'],
        'btn_text' => 'Element hinzufügen',
    ]);

    return $form;
});

echo $mform->show();
```

```php
// Modul-Output

use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$headline = 'REX_VALUE[id=1]';
$text     = 'REX_VALUE[id=2 output="html"]';
$image    = 'REX_VALUE[id=3]';
$layout   = 'REX_VALUE[id=4]';
$items    = MFormRepeaterHelper::decode(5);

// Items filtern, sortieren
$items = MFormRepeaterHelper::sortByField($items, 'title');
$items = MFormRepeaterHelper::limitItems($items, 6);

// Link-Felder normalisieren
$items = MFormOutputHelper::normalizeRepeaterItems($items, ['link']);

foreach ($items as $item) {
    $link = $item['link_normalized'];
    echo '<a href="' . $link['customlink_url'] . '"' . $link['customlink_target'] . '>';
    echo rex_escape($item['title']);
    echo '</a>';
}
```
