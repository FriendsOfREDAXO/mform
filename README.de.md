# MForm - REDAXO Addon für bessere Input-Formulare

![Screenshot](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform7.png?raw=true)

MForm ist ein REDAXO Addon, welches das Erstellen von Modul-Eingabeformularen erheblich erleichtert. Dabei nutzt MForm Templates welche es dem Administrator ermöglichen den Modul-Style seinen Vorstellungen anzupassen. MForm stellt alle gängigen Modul-Input-Formular-Elemente und zusätzlice Widgets bereit welche sich einfach einbinden lassen. MForm eweitert auch **YForm** und **rex_form** um zusätzliche Widgets, z.B. ein Custom-Link-Feld und Image-List für Galerien.

Die beiliegende **Demo-Sammlung** erlaubt das sofortige Ausprobieren von Modul-Codes. Module können direkt installiert und getestet werden. Die Codes sind alle kommentiert.

## Features

- Erstellen von Moduleingaben per PHP
- Ausgabe der Formulare anpassbar über Fragmente
- Custom Widgets für Verlinkung (auch Yform) und Bilder
- Factory die es ermöglicht Formularteile leicht auszulagern
- REDAXO JSON Value Nutzung
- Mehrspaltige Formulare
- Inline-Formular-Elemente
- Modul-Beispiele zur direkten Installation
- HMTL5-Formular-Elemente
- SQL-Felder
- Collapse, Tabs
- Accordions Wrapper Elemente Via Checkbox
- Radio oder Select steuerbare Collapse Elemente
- Durchgehende MBlock Kompatibilität
- Datalists

**Hinweise**

- Der MForm Formular-Builder ist ausschließlich dafür geeignet REDAXO Modul-Input-Formulare zu generieren!
- Aktuell ist das Imagelist-Widget nicht mblock-kompatibel

## Installation

MForm kann direkt über den Redaxo-Installer Installiert werden. [MForm Redaxo Addon Page](http://www.redaxo.org/de/download/addons/?addon_id=967&searchtxt=mform&cat_id=-1)

1. In REDAXO einloggen
2. Im Backend unter "Installer > Neue herunterladen" "MForm" suche und unter "Funktion" "ansehen" klicken
3. Bei der aktuelle Version in der Liste unter "Funktion" "herunterladen" klicken
4. Unter "AddOns" MForm installieren und aktivieren

## Usage

MForm muss im Modul-Input eines REDAXO Moduls als PHP Code notiert werden.

### Instanziierung  

```php
// instantiate
$MForm = MForm::factory();
```

Es können beliebig viele MForm Formulare erzeugt werden, welche je nach dem auch direkt als Element-Properties zu instazieren sind.

```php
// instantiate
$MForm = MForm::factory() // init 
    ->addFieldsetArea('My fieldset', MForm::factory() // use fieldset method and init new mform instance 
            ->addTextField(1, ['label' => 'My input']) // add text field with rex_value_id 1 and label attribute
    );
```

### Formularelemente

Die wesentlichen Formularelemente die MForm bereitstellt werden durch Methoden hinzugefügt.

```php
$MForm = MForm::factory()
    ->addHeadline("Headline") // add headline
    ->addTextField(1, ['label' => 'Input', 'style' => 'width:200px']); // add text field with rex_value_id 1
```

Alle MForm Methoden erwarten optional Attribute, Parameter und Optionen. Diese können auch durch Setter nachträglich dem Element zugewiesen werden.

```php
// add text field
$MForm = MForm::factory()
    ->addTextField(1) // add text field with rex_value_id 1
    ->setLabel('Text Field') 
    ->setAttributes(['style' => 'width:200px', 'class' => 'test-field']);
```

Der `REX_VALUE-Key` muss jeder Formular-Input-Methode als Pflichtfeld übergeben werden. Informative Elemente benötigen keine ID.

##### Full JSON Value Support

MForm unterstützt `REX_VALUE-ARRAYs` wodurch es praktisch keine `REX_VALUE`-Limitierung mehr gibt. Zu beachten ist, dass jeder x.0 Key als Sting übergeben werden muss.

```php
// add text field
$MForm = MForm::factory()
    ->addTextField("1.0")
    ->addTextField(1.1)
    ->addTextField("1.2.Titel");
```

### Formular erzeugen

Um das komponierte Formular erzeugen zu lassen muss muss die `show` Methode genutzt werden.

```php
 // create output
echo $MForm->show();

// without var
echo MForm::factory()
    ->addTextField(1, ['label' => 'Input', 'style' => 'width:200px']) // add text field with rex_value_id 1
    ->show();
```

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

**Projekt-Lead**

[Joachim Dörr](https://github.com/joachimdoerr)
