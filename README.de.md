# MForm – REDAXO Addon

![Poster](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform8.png?raw=true)

MForm macht den Aufbau von REDAXO-Modul-Eingaben angenehm. Statt rohem HTML-Formular-Markup wird alles in sauberem PHP definiert — vom einfachen Textfeld bis hin zum vollständig verschachtelten, copy-paste-fähigen Repeater mit bedingter Anzeige von Feldbereichen.

Nicht nur für Module: MForm erweitert auch **YForm** und **rex_form** um zusätzliche Widgets wie das Custom-Link-Feld, Bildlisten oder den nagelneuen ColorSwatch-Picker.

## Was MForm kann

- Modul-Eingabeformulare per fluentem PHP-API (`MForm::factory()->addTextField(...)->...->show()`)
- **Flex-Repeater** für dynamisch wiederholbare Formularzeilen — mit Verschachtelung, Copy/Paste und Aktiv/Inaktiv pro Item
- Wiederverwendbare Formularlogik via **Template-API** (`registerTemplate`, `fromTemplate`, `applyTemplate`)
- Hilfsklassen für die Repeater-Ausgabe: `MFormRepeaterHelper::decode()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()`
- YForm Value-Types: `custom_link`, `custom_link_multi`, `color_swatch`
- Wachsende Bibliothek **installierbarer Demo-Module** direkt aus dem REDAXO-Backend

## Schnellbeispiel

```php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addTabElement('Inhalt', MForm::factory()
        ->addTextField(1, ['label' => 'Headline'])
        ->addColorSwatchField(2, [
            '#ffffff' => 'Weiß',
            '#111111' => 'Schwarz',
            '.bg-primary' => ['label' => 'Primär', 'preview' => '#0d6efd'],
        ], ['label' => 'Hintergrund'])
    )
    ->addTabElement('Items', MForm::factory()
        ->addRepeaterElement(3, MForm::factory()
            ->addTextField('title', ['label' => 'Titel'])
            ->addMediaField('image', ['label' => 'Bild'])
        , true, true, ['min' => 1, 'max' => 10, 'copy_paste' => true])
    )
    ->show();
```

## Dokumentation & Tutorial

Die vollständige Dokumentation ist direkt im REDAXO-Backend unter **MForm → Docs** verfügbar.

Für eine Schritt-für-Schritt-Einführung gibt es den **[FOR-Tutorial-Bereich](https://friendsofredaxo.github.io/tricks/)** mit MForm-spezifischen Beiträgen.

Wer von MBlock migriert, findet den Einstieg in [docs/08_mblock_migration.md](docs/08_mblock_migration.md).

## Installation

Direkt über den REDAXO-Installer installierbar — nach `mform` suchen, herunterladen und aktivieren.

## Lizenz

[MIT Lizenz](LICENSE.md)

## Credits

**Project Leads**

- [Joachim Dörr](https://github.com/joachimdoerr)
- [Thomas Skerbis](https://github.com/skerbis)

**Flex-Repeater**

[Thorben eaCe](https://github.com/eaCe)

**Docs & Testing**

[alexplusde](https://github.com/alexplusde)

**Ein Projekt von [Friends Of REDAXO](https://github.com/FriendsOfREDAXO)**


MForm erleichtert die Erstellung von REDAXO-Modul-Eingaben. Mit MForm kann man nicht nur Formulare erstellen, sondern diese dank flexibler Templates auch optisch genau nach eigenen Wünschen gestalten. Man kann alle REDAXO-Standard-Formularelemente erstellen und erhält einige Extra-Widgets, die sich leicht in Module einfügen lassen.

**Aber da hört’s noch nicht auf!** 
MForm pimpt auch YForm und rex_form mit zusätzlichen Widgets auf. Benutzerdefiniertes Link-Feld oder eine schicke Image-List? Kein Problem, MForm hat die Lösung.

## Neu in Version 9
Version 9 legt den Fokus auf einen robusteren und redaktionsfreundlichen Workflow.

- Neuer Flex-Repeater im Backend mit stabiler Initialisierung (auch in dynamischen Kontexten)
- Neuer Aktiv/Inaktiv-Status pro Repeater-Item (Auge): editierbar bleiben, aber bei der Ausgabe ausblenden
  - Der Status ist im Header sofort sichtbar: gefuellter Punkt (gruen = aktiv, rot = offline)
- **Kopieren / Einfügen für Flex-Repeater** – `copy_paste => true` an `addRepeaterElement()`: einzelnes Item kopieren, als neues Element am Ende einfügen
- TinyMCE-Kompatibilität im Repeater verbessert (Add/Move/Sort/Remove)
- Neues Linklist/Medialist-Repeater-Widget mit robuster Popup-Übernahme
- Medialist-Widget mit integriertem View-Switch (Liste/Raster) per Toolbar-Button
- Neue API `addCustomLinkMultipleField(...)` – Repeater-basiertes Multi-Link-Feld; Single-Format bleibt unveraendert
- Neue Conditional-API über `addConditionalFieldsetArea(...)`
- **Neue YForm Value-Types** aus dem mform-Paket:
  - `custom_link` – unterstützt jetzt `anchor: 0` zum Ausblenden des Anker-Buttons; Bug im Classic-Template behoben
  - `custom_link_multi` – mehrere Links pro YForm-Feld, gespeichert als JSON-Array
- **Neuer Helfer `MFormRepeaterHelper::decode()`** – Repeater-Werte in einem Aufruf ohne Offline-Items dekodieren
- Demo-Sammlung erweitert (Conditional Fields, Copy/Paste-Repeater)
- Doku erweitert (Repeater-Output-Filter, Conditional Fields)

## Features

### Grundlegende Funktionalitäten
- **Erstellen von Moduleingaben per PHP**: Die Basis, um mit MForm zu arbeiten.
- **Mehrspaltige Formulare**: Layout-Optionen zur Strukturierung der Formulare.
- **Inline-Formular-Elemente**: Für eine kompakte Formulargestaltung.
- **HTML5-Formular-Elemente**: Nutzung moderner Webstandards.
- **Datalists**: Für verbesserte Eingabeoptionen in Formularen.

### Erweiterte Gestaltung und Interaktivität
- **Custom Widgets für Verlinkung (auch Yform) und Bilder**: Spezielle Widgets für häufig benötigte Funktionen.
- **Factory die es ermöglicht Formularteile leicht auszulagern**: Vereinfacht die Wiederverwendung von Formularkomponenten.
- **Collapse, Tabs, Accordions**: Elemente zur Gestaltung dynamischer, interaktiver Formulare.
- **Wrapper Elemente Via Checkbox, Radio oder Select steuerbare Collapse Elemente**: Bietet interaktive Steuerungselemente für die Benutzerführung.
- **Ausgabe der Formulare anpassbar über Fragmente**: Ermöglicht die flexible Gestaltung der Formulardarstellung.

### Spezielle Funktionen
- **Integrierter Formular-Repeater**: Ersetzt MBlock und erlaubt verschachtelte Formularelemente.
- **REDAXO JSON Value Nutzung**: Integration von REDAXO spezifischen Datenstrukturen.
- **SQL-Felder**: Direkte Einbindung von Datenbankabfragen.
- **Durchgehende MBlock Kompatibilität**: Gewährleistet Kompatibilität mit bestehenden MBlock-Installationen.
- **Modul-Beispiele zur direkten Installation**: Bietet sofort einsatzbereite Vorlagen für verschiedene Anwendungsfälle.

## Formular-Repeater

Der Formular-Repeater ermöglicht es, Formularelemente dynamisch zu wiederholen und dabei eine Verschachtelung in mehreren Ebenen zu realisieren.

```php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addRepeaterElement(
        1,
        MForm::factory()
            ->addFieldsetArea('Team member', MForm::factory()
                ->addTextField('name', ['label' => 'Name'])
                ->addMediaField('image', ['label' => 'Avatar'])
            ),
        true,
        true,
        ['min' => 1, 'max' => 10]
    )
    ->show();
```

> **MBlock-Migration:** Hinweise zur Migration bestehender MBlock-Module gibt es im [Migrationsleitfaden](docs/08_mblock_migration.md).

## Installation

MForm kann direkt über den Redaxo-Installer installiert werden. [MForm Redaxo Addon Page](http://www.redaxo.org/de/download/addons/?addon_id=967&searchtxt=mform&cat_id=-1)

1. In REDAXO einloggen
2. Im Backend unter "Installer > Neue herunterladen" "MForm" suche und unter "Funktion" "ansehen" klicken
3. Bei der aktuellen Version in der Liste unter "Funktion" "herunterladen" klicken
4. Unter "AddOns" MForm installieren und aktivieren

## Ausgabe

MForm nutzt die von REDAXO bereitgestellten REDAXO Variablen. Entweder als klassische oder als JSON-Values.
Informationen hierzu in der [REDAXO Doku](https://www.redaxo.org/doku/main/redaxo-variablen).

## Lizenz

MForm ist unter der [MIT Lizenz](LICENSE.md) lizenziert.

## Changelog

siehe [CHANGELOG.md](https://github.com/FriendsOfREDAXO/mform/blob/master/CHANGELOG.md)

## Autor

**Friends Of REDAXO**

- <http://www.redaxo.org>
- <https://github.com/FriendsOfREDAXO>

## Credits

**Projekt-Lead**

[Joachim Dörr](https://github.com/joachimdoerr)

**2nd. Maintainer**

[skerbis](https://github.com/skerbis)

**Mform-Repeater**

[Thorben eaCe](https://github.com/eaCe)

**Docs & Testing**

[alexplusde](https://github.com/alexplusde)



