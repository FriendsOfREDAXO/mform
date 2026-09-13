# MForm 10 – Was ist neu?

MForm 10 setzt PHP 8.4 voraus, lässt MBlock hinter sich und bringt den Flex-Repeater, den Form Builder und die Widgets auf einen gemeinsamen Stand. Diese Seite fasst zusammen, was sich gegenüber 9.5 ändert, und verweist auf die Details. Die Historie von MForm 9 steht weiter unten.

## Voraussetzungen und Umstieg

- **PHP 8.4** ist Pflicht. Der Parser arbeitet mit `\Dom\HTMLDocument`, die libxml-Umwege sind weg.
- **MBlock wird nicht mehr unterstützt.** Module mit `MBlock::show()` laufen technisch weiter, werden aber nicht mehr getestet. Der Weg ist der Flex-Repeater: [Migration von MBlock](08_mblock_migration.md) beschreibt Assistent, Konsole und die manuelle Migration.
- Die stabile Linie 9.5.x wird im Branch `9.x` gepflegt. Gespeicherte Werte bleiben lesbar; das neue Repeater-Speicherformat ist Opt-in.

### Umstieg von 9.5: Checkliste

Eine Datenmigration gibt es nicht, das Update ist ein normales Addon-Update. Vorher prüfen:

- **PHP 8.4** auf dem Server, sonst startet das Addon nicht.
- **MediaPlace** mindestens 2.0.0, falls installiert (gilt seit 9.4).
- **Gespeicherte Werte** bleiben unverändert: `REX_VALUE`, `REX_MEDIA`, Custom-Link-Strings, Listen und das Repeater-JSON sind identisch zu 9.5. Der Umschlag mit Versionsmarker kommt nur mit `'data_version' => 2` je Feld.
- **API**: alle `add*`-Methoden, Optionen und Ausgabehelfer aus 9.5 funktionieren unverändert, nichts ist als veraltet markiert oder entfernt.
- **MBlock**: Module mit `MBlock::show()` laufen technisch weiter, werden aber nicht mehr getestet. Umstellen mit dem Assistenten „MBlock zu Repeater“, wann es passt.
- **Eigenes CSS / eigene Fragmente**: Widgets nutzen gemeinsame Tokens und sind eckig, das Repeater-Label trägt kein `control-label` mehr, Wrapper und Feldzeilen kommen in beiden Renderpfaden aus `mform_wrapper.php` und `mform_default.php`. Wer diese Fragmente im Projekt überschreibt oder Widgets per CSS anpasst, schaut einmal drauf.
- **Tooltip-Klasse**: heißt jetzt `mform-info-tooltip`, `mblock-info-tooltip` bleibt als zweite Klasse am Element.

## Repeater

- **Bedienung:** Kopfzeile mit Label, Zähler und Aktionen, „Hinzufügen“ als durchgehender Streifen unter der Liste, auch in verschachtelten Repeatern. Details: [Repeater](07_repeater.md#standardverhalten-und-optionen).
- **Speicherformat mit Version:** `'data_version' => 2` schreibt einen Umschlag `{"__v":2,"items":[...]}`, Standard bleibt die reine Liste, alle Ausgabehelfer lesen beide Formate. Details: [Repeater](07_repeater.md#speicherformat-und-version-ab-100).
- **Typisierte Items:** `MFormOutput::from(1)->items()` liefert `MFormRepeaterItem`-Objekte mit `media()`, `article()`, `dataset()`, `url()` und `items()` für verschachtelte Repeater. Details: [MFormOutput](15_mform_output.md#typisierte-items).
- **Gemeinsame Renderpfade:** Wrapper und Feldzeilen kommen im klassischen Formular und im Repeater aus denselben Theme-Fragmenten (`mform_wrapper.php`, `mform_default.php`). Details: [Templates](09_templates.md#wrapper-fragment-ab-100).

## Felder

- **HTML5-Felder:** `addNumberField()`, `addRangeField()` (mit Live-Wert), `addDateField()`, `addDateTimeField()`, `addTimeField()`, `addEmailField()`, `addColorField()`. Details: [Grundlagen](01_basics.md#weitere-html5-elemente).
- **Tags-Feld:** `addTagsField()` für Schlagworte als Pills mit Vorschlägen, `allow_new` und `max`, gespeichert kommasepariert. Details: [Erweiterte Beispiele](06_advanced.md#beispiel-addtagsfield--schlagworte-als-pills-ab-100).
- **Mehrere Bedingungen:** `setVisibleIf()` nimmt eine Liste, `addVisibleIf()` hängt an, `setVisibleIfLogic('any')` lässt eine genügen, `setHiddenIf()` blendet aus; `addConditionalFieldsetArea()` ebenfalls mit Liste. Details: [Erweiterte Beispiele](06_advanced.md#beispiel-visible_if-ohne-wrapper).
- **Eigene Feldtypen:** `MForm::registerFieldType($type, $renderer)` registriert Typen aus Fremd-Addons, `addCustomField()` setzt sie ins Formular, derselbe Renderer läuft im Formular und im Repeater. Details: [API-Referenz](13_api_reference.md#eigene-feldtypen-registry-ab-100).
- **Barrierefreiheits-Prüfung für Medien-Felder:** Option `a11y` prüft ALT-Text und weitere Metainfo-Felder gegen den Medienpool und zeigt Befunde direkt unter dem Widget; global und je Feld abschaltbar, optional als Standardprüfung. Details: [Barrierefreiheit](16_a11y.md).

## Widgets und Integrationen

- **Linkmap:** Mit installiertem [Linkmap](https://github.com/FriendsOfREDAXO/linkmap) öffnen Custom-Link, Link- und Linklist-Felder das Overlay, `ylink`-Datensätze kommen aus dem Linkmap-Picker. Details: [Custom-Link-Widget](03_customlink.md#linkmap-integration-ab-100).
- **Ein Design für alle Widgets:** gemeinsame Tokens in `assets/css/mform-tokens.css`, Light und Dark, abgestimmt auf Linkmap und MediaPlace, Felder eckig wie im Backend. Details: [Templates](09_templates.md#design-tokens-ab-100).

## Form Builder

- Vollständige Palette: Collapse, Accordion, Column, Inline, Radio Image/Icon/Color, Text/Textarea (readonly) neben Tab, Fieldset, Modal und Repeater; Medien-Felder mit A11y-Checkbox.
- Export und Import des Builder-Stands als JSON (`mform-builder.json`).
- Vorschau, Eingabe- und Ausgabecode liegen als auf- und zuklappbare Abschnitte unter der Baufläche, nur die Vorschau ist anfangs offen: das Formular wird über die MForm-Engine gerendert, im Backend-Theme, automatisch nach jeder Änderung. Offene Abschnitte werden gemerkt, „Alle auf / zu“ und „Eingabecode kopieren“ sitzen in der Leiste darüber.
- Bedingungs-Editor: unter „Sichtbarkeit an Bedingungen koppeln“ beliebig viele Regeln aus Quellfeld, Operator und Wert, Verknüpfung „alle“ oder „eine genügt“; der Builder erzeugt `setVisibleIf()`/`addVisibleIf()` bzw. `addConditionalFieldsetArea()` mit Liste.

## Werkzeuge

- **Migrationsassistent „MBlock zu Repeater“** in sechs Schritten (Inventar, Code, Modul-Kopie, Daten mit Backup und Rollback, Slices umhängen, YForm-Felder), auf der Konsole `mform:migrate`.
- **Linter** `mform:lint` findet MBlock-Reste, numerische Widget-Ids und Präfix-Feldnamen in Repeater-Formularen.
- **Einstellungen:** neue Backend-Seite für die A11y-Prüfung.
- **Test-Suite:** PHPUnit (`unit` ohne REDAXO, `redaxo` gegen eine Installation, Render-Snapshots für beide Pfade) und ein Playwright-Smoke-Test, alles in CI.

---

# Historie: MForm 9

## Update 9.4.0: MediaPlace-Unterstützung für Medien-Widgets

- Custom-Link (Einzelmedium + Vorschau-Button), das Medialisten-Widget (`REX_MEDIALIST`/`REX_CUSTOM_MEDIALIST`) und das ältere Bilderlisten-Widget öffnen jetzt automatisch das [MediaPlace](https://github.com/FriendsOfREDAXO/mediaplace)-Overlay statt des klassischen Medienpool-Popups, sofern MediaPlace installiert und aktiv ist.
- Ohne MediaPlace ändert sich nichts – die klassischen Popups bleiben der Fallback.
- Die Erkennung läuft zentral über `assets/js/mediaplace-bridge.js`, auf die alle Widget-Skripte zurückgreifen.

---

## Update 9.3.1: Dropdown-Overflow und Datensatz-Link-Labels

- Offene Dropdown-Menüs in Flex-Repeater-Items werden nicht mehr am Item-Rahmen abgeschnitten.
- Das Custom-Link-Widget zeigt bei Werten wie `rex-team-members://15` nach Möglichkeit einen lesbaren Datensatznamen statt nur des Rohwerts.
- `ylink`-Quellen können jetzt optional definieren, aus welchen Spalten ein gespeicherter Datensatz im Widget beschriftet wird.
- Unterstützt werden sowohl Array-Konfigurationen mit `label_columns` als auch die String-Syntax `Label::tabelle::dropdown_spalte::spalte1|spalte2`.

---

## Update 9.1: Tabs und Form Builder

- Tabs werden ID-frei gerendert und sind dadurch stabil in verschachtelten Kontexten (inkl. FlexRepeater).
- `addTabElement()` kann direkt in Repeater-Item-Formularen genutzt werden.
- Optional pro Tab: `tab-icon`, `tab-style => modern`, `tab-layout => vertical`.
- Der Visual Form Builder unterstuetzt diese drei Tab-Optionen ebenfalls in den Tab-Eigenschaften.

---

## Update 9.1.4: Neuer MBlock-Konverter-Workflow

Das Migrationswerkzeug (`Backend -> MForm -> MBlock zu Repeater`) wurde fuer reale Bestandsmodule deutlich erweitert.

- Erzeugt auf Wunsch ein **neues konvertiertes Modul** mit Prefix `mfr_` und Timestamp
- Bietet eine **Slice-Umhaengung** auf ein Zielmodul direkt im Tool
- Enthält eine **Revert-Funktion** (letzte Umhaengung rueckgaengig)
- Unterstuetzt **Legacy-Key-Mapping** in der Datenmigration
    - z. B. `1 -> link`
    - optional per JSON fuer mehrere Keys
- Liefert klare Rueckmeldungen nach Konvertierung, Dry-Run, Speichern/Umhaengen
- Springt nach Aktionen automatisch zum passenden Abschnitt (Anker-Navigation)

Wichtig: Der Konverter deckt viele Standardfaelle ab, aber nicht alle Sonderfaelle. Spezielle Konfigurationen, externe Dateien oder projektindividuelle Logik muessen ggf. manuell nachgezogen werden.

---

## Fixes & Verbesserungen in beta4

### Flex-Repeater: Alle Link- und Media-Widgets vollständig unterstützt

Folgende Widgets rendern jetzt korrekt im Flex-Repeater – zuvor wurde fälschlicherweise ein Fehler-Platzhalter angezeigt:

| Widget | Methode |
|--------|---------|
| Custom-Link (alle Modi) | `addCustomLinkField()` |
| Custom-Link (mehrere Links) | `addCustomLinkMultipleField()` |
| REDAXO-Artikel-Link | `addLinkField()`, `addMFormLinkField()` |
| Medium | `addMediaField()`, `addMFormMediaField()` |
| Bilderliste | `addImagelistField()` |

Readonly-Felder (`addTextReadonlyField()`, `addTextareaReadonlyField()`) werden ebenfalls korrekt gerendert.

### Custom-Link: Artikelname bei Vorab-Wert

Wird ein Custom-Link-Widget im Flex-Repeater mit einem bereits gespeicherten Wert initialisiert (z. B. `8` oder `redaxo://8`), wird der zugehörige Artikelname per AJAX aufgelöst und im sichtbaren Textfeld angezeigt – statt der nackten ID.

### Custom-Link: Eingabe-Validierung

- **mailto**: Ungültige E-Mail-Adressen werden mit Fehlermeldung abgelehnt
- **tel**: Nur Ziffern, `+`, `-`, Leerzeichen und Klammern zulässig

### Custom-Link-Multi: Papierkorb-Icon

Der Entfernen-Button je Eintrag zeigt jetzt ein `fa-trash`-Symbol.

---

## Flex-Repeater – wiederholende Inhalte

Der Flex-Repeater war bereits vorhanden. In MForm 9 wurde er technisch überarbeitet und deutlich robuster in der REDAXO-Umgebung umgesetzt.

Der Flex-Repeater deckt nun die bekannten Funktionen von MBlock vollständig ab.

- Felder werden als JSON in einem REX_VALUE gespeichert
- Drag & Drop Sortierung inklusive
- Editor-Support in Flex-Repeater-Zeilen: TinyMCE, CKE5 und MarkdownEditor
- `MFormRepeaterHelper` für die Ausgabe im Frontend
- **Neu in v9:** Hilfsmethoden `decode()`, `prepareItemsForOutput()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()`

→ [07_repeater.md](07_repeater.md)

---

## Neue Feldtypen

### Bedingte Feldanzeige

`addConditionalFieldsetArea()` blendet Felder anhand des Werts eines anderen Felds ein oder aus – ohne JavaScript selbst schreiben zu müssen.

→ [06_advanced.md](06_advanced.md)

### Toggle-Checkbox

`addToggleCheckboxField()` – moderne Ja/Nein-Umschalter als Alternative zur klassischen Checkbox.

→ [06_advanced.md](06_advanced.md)

### Grafische Radio-Auswahl

- `addRadioImgField()` – Layoutvorschau als Auswahlfeld (via `LayoutPreviewBuilder`)
- `addRadioIconField()` – Icon-basierte Auswahl
- `addRadioColorField()` – Farbauswahl als Radio-Buttons

→ [06_advanced.md](06_advanced.md)

### Modal – Sub-Formular im Bootstrap-Modal

`addModalElement()` – öffnet ein Bootstrap-Modal mit einem eigenen Sub-Formular. Ideal für Zeilen-Einstellungen in Repeatern oder optionale Hilfe-Dialoge:

- Trigger-Button direkt im Formular, keine eigene Seite/Popup nötig
- Button-Ausrichtung: `'left'`, `'center'`, `'right'`
- Beliebige Felder im Modal (Text, Select, Checkbox, Repeater – kein TinyMCE)
- Vollständig im Flex-Repeater unterstützt

```php
->addModalElement('Einstellungen', MForm::factory()
    ->addSelectField('bg', ['' => 'Standard', 'bg-dark' => 'Dunkel'], ['label' => 'Hintergrund'])
    ->addCheckboxField('fullwidth', [1 => 'Volle Breite'], ['label' => 'Layout'])
, 'btn-default', 'center')
```

→ [05_wrapper.md](05_wrapper.md)

---

### ColorSwatch – Farbwähler mit Input und Popup

`addColorSwatchField()` – moderner Farbwähler als Erweiterung eines Text-Inputs:

- Kleines Farbvorschau-Quadrat links im Input
- Farbpaletten-Button öffnet Popup mit vordefinierten Swatches
- Direkteingabe weiterhin möglich
- Unterstützt **Hex-Farbwerte** (`#2f77bc`) und **CSS-Klassennamen** (`.bg-primary`) als gespeicherten Wert
- CSS-Klassen-Swatches können mit optionaler `preview`-Farbe dargestellt werden

```php
->addColorSwatchField('color', [
    '#ffffff'     => 'Weiß',
    '#111111'     => 'Schwarz',
    '.bg-primary' => ['label' => 'Primär', 'preview' => '#2f77bc'],
], ['label' => 'Farbe'])
```

→ [06_advanced.md](06_advanced.md)

### CheckboxGroup-Widget

- `addCheckboxGroupField()` – visuelle Mehrfachauswahl als Pill-/Tag-Buttons
- Optionaler Radio-Mode über `mode => radio` für Einzelauswahl im gleichen Widget
- Horizontales und vertikales Layout über `layout`

→ [12_checkbox_group.md](12_checkbox_group.md)

### Editor-Unterstützung in MForm

MForm 9 unterstützt moderne Editor-Integrationen auch in dynamischen Kontexten wie Flex-Repeatern:

- TinyMCE
- CKE5
- MarkdownEditor

Hinweis: Die jeweilige Editor-Initialisierung erfolgt weiterhin durch das entsprechende Addon.

→ [07_repeater.md](07_repeater.md)

---

## Verbesserte Link- und Media-Widgets

### Custom-Link-Widget

`addCustomLinkField()` unterstützt jetzt alle Link-Typen (intern, extern, Media, Mail, Tel) in einem einzigen Widget.

→ [03_customlink.md](03_customlink.md)

### Mehrfach-Links

`addCustomLinkMultipleField()` – neu in MForm 9. Speichert mehrere Links als JSON-Array in einem Feld.

→ [03_customlink.md](03_customlink.md)

### MForm-natives Media-Widget

`addMFormMediaField()` – Alternative zu `addMediaField()` ohne Reindex-Problem beim Klonen in MBlock.

→ [02_redaxo.md](02_redaxo.md)

### Classic Widgets ohne Klon-Probleme

`MForm::useCustomLinkForClassicWidgets(true)` lässt `addMediaField()` / `addLinkField()` intern das Custom-Link-Widget verwenden. Das Speicherformat (`REX_MEDIA_n` / `REX_LINK_n`) bleibt identisch.

→ [02_redaxo.md](02_redaxo.md)

### Link-Ausgabe normalisieren

`MFormOutputHelper::createLinkData()` normalisiert Custom-Link-Werte aus String- und Array-Format zu einem einheitlichen Ausgabe-Array.

→ [03_customlink.md](03_customlink.md)

---

## MBlock-Kompatibilität (bis MForm 9)

MForm 9 war mit bestehenden MBlock-Modulen kompatibel. Seit MForm 10 wird MBlock nicht mehr unterstützt, siehe oben und [08_mblock_migration.md](08_mblock_migration.md).

---

## Dokumentationsübersicht

| Datei | Inhalt |
|-------|--------|
| [01_basics.md](01_basics.md) | Grundlagen, Text- und Eingabefelder, HTML5-Felder |
| [02_redaxo.md](02_redaxo.md) | Media- & Link-Elemente, REX_VALUE-Keys |
| [03_customlink.md](03_customlink.md) | Custom-Link-Widget, Mehrfach-Links, Ausgabe-API, Linkmap |
| [04_imagelist.md](04_imagelist.md) | Bildliste, Medialist, Linklist |
| [05_wrapper.md](05_wrapper.md) | Fieldset, Collapse, Accordion, Tabs, Columns, Inline, Modal |
| [06_advanced.md](06_advanced.md) | Attribute, Optionen, ConditionalFieldset, visible_if, RadioImg, ColorSwatch, Templates |
| [07_repeater.md](07_repeater.md) | Flex-Repeater, Optionen, Speicherformat, Frontend-Hilfsmethoden |
| [08_mblock_migration.md](08_mblock_migration.md) | Migration von MBlock: Assistent, Konsole, manuell |
| [09_templates.md](09_templates.md) | Template-API, Design-Tokens, Wrapper-Fragment |
| [10_outside_modules.md](10_outside_modules.md) | MForm außerhalb von Modulen verwenden |
| [11_tutorial_modul.md](11_tutorial_modul.md) | Komplettes Modul-Tutorial |
| [12_checkbox_group.md](12_checkbox_group.md) | CheckboxGroup-Widget inkl. Radio-Mode |
| [13_api_reference.md](13_api_reference.md) | API-Referenz öffentlicher Klassen und Methoden |
| [14_fragments_output.md](14_fragments_output.md) | Ausgabe über REDAXO-Fragmente und mfragment-Komponenten |
| [15_mform_output.md](15_mform_output.md) | MFormOutput: fluente Ausgabe, typisierte Items |
| [16_a11y.md](16_a11y.md) | Barrierefreiheit: Metadaten-Prüfung für Medien |
