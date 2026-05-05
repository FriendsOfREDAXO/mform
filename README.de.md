# MForm - REDAXO Addon

![Poster](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform8.png?raw=true)

MForm erleichtert die Erstellung von REDAXO-Modul-Eingaben. Mit MForm kann man nicht nur Formulare erstellen, sondern diese dank flexibler Templates auch optisch genau nach eigenen Wünschen gestalten. Man kann alle REDAXO-Standard-Formularelemente erstellen und erhält einige Extra-Widgets, die sich leicht in Module einfügen lassen.

**Aber da hört’s noch nicht auf!** 
MForm pimpt auch YForm und rex_form mit zusätzlichen Widgets auf. Benutzerdefiniertes Link-Feld oder eine schicke Image-List? Kein Problem, MForm hat die Lösung.

## Neu in Version 9
Version 9 legt den Fokus auf einen robusteren und redaktionsfreundlichen Workflow.

- Neuer Flex-Repeater im Backend mit stabiler Initialisierung (auch in dynamischen Kontexten)
- Neuer Aktiv/Inaktiv-Status pro Repeater-Item (Auge): editierbar bleiben, aber bei der Ausgabe ausblenden
- TinyMCE-Kompatibilität im Repeater verbessert (Add/Move/Sort/Remove)
- Neues Linklist/Medialist-Repeater-Widget mit robuster Popup-Übernahme
- Neue Conditional-API über `addConditionalFieldsetArea(...)`
- Demo-Sammlung erweitert (u. a. Conditional Fields)
- Doku erweitert (Repeater-Output-Filter, Conditional Fields)

## Features

### Grundlegende Funktionalitäten
- **Erstellen von Moduleingaben per PHP**: Die Basis, um mit MForm zu arbeiten.
- **Mehrspaltige Formulare**: Layout-Optionen zur Strukturierung der Formulare.
- **Inline-Formular-Elemente**: Für eine kompakte Formulargestaltung.
- **HMTL5-Formular-Elemente**: Nutzung moderner Webstandards.
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

### Migration von MBlock zu MForm 8 

Der neue Repeater ist nur eingeschränkt mit MBlock kompatibel.

Akuell funktionieren nicht bei einer Migration: 

- CustomLinkField // Converter: https://friendsofredaxo.github.io/tricks/addons/mform/custom_link_converter
- addMediaListField
- addLinkListField

***MBlock-Modul*** 

```php
// Basis-ID für die Verwaltung der Formularelemente
$id = 1;

// Initialisierung von MForm
$mform = new MForm();

// Hinzufügen eines Feldsets
$mform->addFieldsetArea('Team member');

// Hinzufügen eines Textfelds, wobei dynamisch auf ein JSON-Format verwiesen wird
$mform->addTextField("$id.0.name", array('label' => 'Name'));

// Hinzufügen eines Medienfeldes, das durch MBlock in JSON gespeichert wird
$mform->addMediaField(1, array('label' => 'Avatar'));

// Ausgabe des Formulars mit MBlock, welches die dynamische Handhabung der Blöcke erlaubt
echo MBlock::show($id, $mform->show(), array('min' => 2, 'max' => 4));
```

***Das gleiche Modul in MForm 8*** 

Zur Ermittlung der benötigten Feld-Keys sollte man ggf. vorab einen Dump erzeugen. 
Zu beachten: Aus dem Mediafield 1 im urpsrünglichen MBlock-Modul wird: `'REX_MEDIA_1'`

```php
use FriendsOfRedaxo\MForm;

// Initialisierungs-ID des Repeaters mit der Basis-ID des ursprünglichen MBlock-Abschnittes
$id = 1;

// Erstellen einer neuen MForm-Instanz mit der Factory-Methode und direkte Integration eines Repeaters
echo MForm::factory()
    ->addRepeaterElement(
        $id, 
        MForm::factory()
            ->addFieldsetArea('Team member', 
                MForm::factory()
                    ->addTextField('name', ['label' => 'Name'])
                    ->addMediaField('REX_MEDIA_1', ['label' => 'Avatar'])
            ),
        true, 
        true, 
        ['min' => 2, 'max' => 4]
    )
    ->show();
```



## Installation

MForm kann direkt über den Redaxo-Installer Installiert werden. [MForm Redaxo Addon Page](http://www.redaxo.org/de/download/addons/?addon_id=967&searchtxt=mform&cat_id=-1)

1. In REDAXO einloggen
2. Im Backend unter "Installer > Neue herunterladen" "MForm" suche und unter "Funktion" "ansehen" klicken
3. Bei der aktuelle Version in der Liste unter "Funktion" "herunterladen" klicken
4. Unter "AddOns" MForm installieren und aktivieren

## Ausgabe

MForm nutzt die von REDAXO bereitgestellten REDAXO Variablen. Entweder als klassische oder als JSON-Values.
Informationen hierzu in der [REDAXO Doku](https://www.redaxo.org/doku/main/redaxo-variablen).

## Lizenz

MForm ist unter der [MIT Lizenz](LICENSE.md) lizensiert.

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



