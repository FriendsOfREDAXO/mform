# MForm 9 – Was ist neu?

MForm 9 ist ein umfassendes Upgrade mit neuen Feldern, einem vollständig neuen Repeater-System und einer verbesserten Link-API. Diese Seite gibt einen Überblick und verweist auf die jeweiligen Details.

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

## MBlock-Kompatibilität

MForm 9 ist vollständig rückwärtskompatibel mit bestehenden MBlock-Modulen. Für eine schrittweise Migration gibt es einen dedizierten Leitfaden.

→ [08_mblock_migration.md](08_mblock_migration.md)

---

## Dokumentationsübersicht

| Datei | Inhalt |
|-------|--------|
| [01_basics.md](01_basics.md) | Grundlagen, Text- und Eingabefelder |
| [02_redaxo.md](02_redaxo.md) | Media- & Link-Elemente, REX_VALUE-Keys |
| [03_customlink.md](03_customlink.md) | Custom-Link-Widget, Mehrfach-Links, Ausgabe-API |
| [04_imagelist.md](04_imagelist.md) | Bildlisten-Feld |
| [05_wrapper.md](05_wrapper.md) | Fieldset, Tabs, Accordion, Columns, Modal |
| [06_advanced.md](06_advanced.md) | Neue Feldtypen, ConditionalFieldset, RadioImg |
| [07_repeater.md](07_repeater.md) | Repeater, FlexRepeater, Frontend-Hilfsmethoden |
| [08_mblock_migration.md](08_mblock_migration.md) | Migration von MBlock-Modulen |
| [09_templates.md](09_templates.md) | Fragment-Templates, Custom-Templates |
| [10_outside_modules.md](10_outside_modules.md) | MForm außerhalb von Modulen verwenden |
| [11_tutorial_modul.md](11_tutorial_modul.md) | Komplettes Modul-Tutorial |
| [12_checkbox_group.md](12_checkbox_group.md) | CheckboxGroup-Widget inkl. Radio-Mode |
| [06_advanced.md](06_advanced.md) | ColorSwatch-Feld, RadioColorField, RadioImgField, ConditionalFieldset |
