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

* Der MForm Formular-Builder ist ausschließlich dafür geeignet REDAXO Modul-Input-Formulare zu generieren!
* Aktuell ist das Imagelist-Widget nicht mblock-kompatibel


## Installation:

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

### Element-Methoden

MForm stellt folgende Element-Methoden bereit: 

* Strukturelle Wrapper-Elemente
  * `addFieldsetArea`
  * `addCollapseElement`
  * `addAccordionElement`
  * `addTabElement`
  * `addColumnElement`
  * `addInlineElement`
* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addHiddenField`
  * `addTextAreaField`
  * `addTextReadOnlyField`
  * `addTextAreaReadOnlyField`
* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`
* Informelle-Elemente
  * `addHtml`
  * `addHeadline`
  * `addDescription`
  * `addAlert`
  * `addAlertDanger`, `addAlertError`
  * `addAlertInfo`
  * `addAlertSuccess`
  * `addAlertWarning`
* System-Button-Elemente
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`
* Custom-Elemente 
  * `addCustomLinkField`
  * `addImagelistField`
  * `addInputField`
* Spezielle `setter`-Methoden
  * `setAttribute`
  * `setAttributes`
  * `setCategory`
  * `setCollapseInfo`
  * `setDefaultValue`
  * `setDisableOption`
  * `setDisableOptions`
  * `setFormItemColClass`
  * `setFull`
  * `setLabel`
  * `setLabelColClass`
  * `setMultiple`
  * `setOption`
  * `setOptions`
  * `setParameter`
  * `setParameters`
  * `setPlaceholder`
  * `setSize`
  * `setSqlOptions`
  * `setTabIcon`
  * `setToggleOptions`
  * `setTooltipInfo`

## Ausgabe 

MForm nutzt die von REDAXO bereitgestellten REDAXO Variablen. Entweder als klassische oder als JSON-Values. 
Informationen hierzu in der [REDAXO Doku](https://www.redaxo.org/doku/main/redaxo-variablen).

## Custom-Link Element

Das Custom MForm Custom-Link-Element ermöglicht es durch den Einsatz eines Feldes mehrere Link-Typen definieren zu können.  
Das Cusotm-Link-Element steht in MForm, YForm und auch als REX_VAR zur Verfügung.  

Die Link Typen des Custom-Link-Elements:

* `data-extern`
* `data-intern`
* `data-media`
* `data-mailto`
* `data-tel`
* `ylink`

Jeder dieser Typen kann aktiviert oder deaktiviert werden. Per default sind folgende Typen aktiv:

* `data-extern`
* `data-intern`
* `data-media`


### Verwendung mit MBlock

Das Custom-Link-Element darf keinen String (wie bei anderen Elementen) in der ID enthalten:  

`$MBlock->addCustomLinkField("$id.0.1",array('label'=>'Link'));`


### Beispiel-Code: 

#### MForm

```php
$mform = new MForm();
$ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']];
$mform->addCustomLinkField(1, ['label' => 'custom', 'data-intern'=>'disable', 'data-extern'=>'enable', 'ylink' => $ylink]);
echo $mform->show();
```

### Als REX_VAR

```html
REX_CUSTOM_LINK[id=5 widget=1 external=1 intern=0 mailto=0 phone=1 media=1 ylink="Countries::rex_ycountries::de_de,CountriesEN::rex_ycountries::en_gb"]
```

#### Auslesen der YLinks per Outputfilter

#### YForm links

Um die  generierten Urls wie `rex_news://1` zu ersetzen, muss das folgende Skript in die `boot.php` des `project` AddOns eingefügt werden.
Der Code für die Urls muss modifiziert werden. 

```php
rex_extension::register('OUTPUT_FILTER', function(\rex_extension_point $ep) {
    return preg_replace_callback(
        '@((rex_news|rex_person))://(\d+)(?:-(\d+))?/?@i',
        function ($matches) {
            // table = $matches[1]
            // id = $matches[3]
            $url = '';
            switch ($matches[1]) {
                case 'news':
                    // Example, if the Urls are generated via Url-AddOn  
                    $id = $matches[3];
                    if ($id) {
                       return rex_getUrl('', '', ['news' => $id]); 
                    }
                    break;
                case 'person':
                    // ein anderes Beispiel 
                    $url = '/index.php?person='.$matches[3];
                    break;
            }
            return $url;
        },
        $ep->getSubject()
    );
}, rex_extension::NORMAL);

```


#### Auslesen der Ylinks manuell: 

```php 
$link = explode("://", $img['link']);

      if (count($link) > 1) {
          // its a table link
          // url AddOn
        $url = rex_getUrl('', '', [$link[0] => $link[1]]); // key muss im url addon übereinstimmen
      } else {
          $extUrl = parse_url($link[0]);

          if (isset($extUrl['scheme']) && ($extUrl['scheme'] == 'http' || $extUrl['scheme'] == 'https')) {
              // its an external link 
              $url = $link[0];
          } else {
              // internal id
              $url = rex_getUrl($link[0]);
          }
      }
```

#### Custom Link auslesen

MForm liefert eine Methode zum Auslesen und Auswerten der Custom-Links. 
`MForm\Utils\MFormOutputHelper::prepareCustomLink(array $item, $externBlank = true)`

Die Methode nimmt ein Array für den Link  an und gibt ein  Array mit verarbeiteten Links zurück. 

```php
$link = '10';
$linkdata = MForm\Utils\MFormOutputHelper::prepareCustomLink(['link' => $link], true);
```

Ergebnis: 

```
^ array:5 [▼
    "link" => "10"
    "customlink_text" => "Artikelname"
    "customlink_url" => "/artikelname"
    "customlink_target" => ""
    "customlink_class" => " intern"
]
```

Benötigt man nur den Link oder möchte man mehr Erkennungsmöglichkeiten realisieren, kann man folgendes Beispiel verwenden.  

Die nachfolgende Funktion dient dazu den von MForm / Mblock generierten CustomLink auszulesen und korrekt zu verlinken. Die Funktion kann in der Ausgabe eines Moduls genutzt werden oder ggf. im Theme- oder Projektaddon verwendet werden. Sie kann auch allgemein dazu verwendet werden, einen unbekannten Link zu identifizieren 

Die Funktion kann in der functions.php vom theme-AddOn oder in der boot.php vom project-AddOn hinterlegt werden:  

```php
// CustomLink-Funktion REX5 / mform / mblock

if (!function_exists('getcustomLink')) {
  function getcustomLink($url,$text) {

  // Wurde ein Wert für $url übergeben?
  if ($url) {

    // Prüfe ob es sich um eine URL handelt, dann weiter
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
    }

    // Ist es eine Mediendatei?
    if (file_exists(rex_path::media($url)) === true) {
       $url = rex_url::media($url);
    }
    else {
        // Ist es keine Mediendatei oder URL, dann als REDAXO-Artikel-ID behandeln
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE and is_numeric($url)) {
            $url = rex_getUrl($url);
        }
    }

   return $url;
   }
  }
}
```


## Lizenz

MForm ist unter der [MIT Lizenz](LICENSE.md) lizensiert.

## Changelog

siehe [CHANGELOG.md](https://github.com/FriendsOfREDAXO/mform/blob/master/CHANGELOG.md)

## Autor

**Friends Of REDAXO**

* http://www.redaxo.org
* https://github.com/FriendsOfREDAXO

**Projekt-Lead**

[Joachim Dörr](https://github.com/joachimdoerr)
