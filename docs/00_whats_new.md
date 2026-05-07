# MForm 9 – Was ist neu?

MForm 9 ist ein umfassendes Upgrade mit neuen Feldern, einem vollständig neuen Repeater-System und einer verbesserten Link-API. Diese Seite gibt einen Überblick und verweist auf die jeweiligen Details.

---

## Repeater – wiederholende Inhalte

**Neu:** `addRepeaterElement()` und `addFlexRepeaterElement()` ermöglichen wiederholende Feldgruppen direkt in MForm – ohne externes Addon.

- Felder werden als JSON in einem REX_VALUE gespeichert
- Drag & Drop Sortierung inklusive
- `MFormRepeaterHelper` für die Ausgabe im Frontend
- Hilfsmethoden: `decode()`, `prepareItemsForOutput()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()`

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
| [05_wrapper.md](05_wrapper.md) | Fieldset, Tabs, Accordion, Columns |
| [06_advanced.md](06_advanced.md) | Neue Feldtypen, ConditionalFieldset, RadioImg |
| [07_repeater.md](07_repeater.md) | Repeater, FlexRepeater, Frontend-Hilfsmethoden |
| [08_mblock_migration.md](08_mblock_migration.md) | Migration von MBlock-Modulen |
| [09_templates.md](09_templates.md) | Fragment-Templates, Custom-Templates |
| [10_outside_modules.md](10_outside_modules.md) | MForm außerhalb von Modulen verwenden |
| [11_tutorial_modul.md](11_tutorial_modul.md) | Komplettes Modul-Tutorial |
