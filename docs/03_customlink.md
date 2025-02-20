# Custom-Link-Widget

Das MForm Custom-Link-Element ermöglicht es durch den Einsatz eines Feldes mehrere Link-Typen definieren zu können.  

Das Custom-Link-Element steht in MForm, YForm und auch als REX_VAR zur Verfügung.  

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

## Modul-Eingabe

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
echo MForm::factory()
    // add fieldset area
    ->addFieldsetArea('MForm Widgets', MForm::factory()
        // custom elements
        ->addCustomLinkField(1, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
    )
    // parse form
    ->show();
```

## Modul-Ausgabe

```php
<?php
dump('REX_VALUE[id=1]');
```

## Verwendung mit MBlock

Das Custom-Link-Element darf keinen String (wie bei anderen Elementen) in der ID enthalten:  

`$MBlock->addCustomLinkField("$id.0.1",array('label'=>'Link'));`

### MForm

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

## Auslesen der Custom-Links

MForm bietet drei Methoden zum Auslesen und Verarbeiten von Custom-Links:

```php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
```

### getCustomUrl()

Gibt die URL für einen Link zurück. Optional kann eine Sprach-ID übergeben werden.

```php
$url = MFormOutputHelper::getCustomUrl($value, $lang);
```

### prepareCustomLink()

Bereitet einen Link auf und gibt zusätzliche Informationen zurück:

```php
$linkdata = MFormOutputHelper::prepareCustomLink(['link' => $value], true);
```

Ergebnis:
```php
[
    "link" => "10"
    "customlink_text" => "Artikelname"
    "customlink_url" => "/artikelname"
    "customlink_target" => ""
    "customlink_class" => " internal"
]
```

### getCustomLinkUrl() [NEU]

Diese neue Methode extrahiert speziell die URL aus einem Custom-Link. Sie ist flexibler als `getCustomUrl()` und kann verschiedene Eingabeformate verarbeiten:

```php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

// Einfache Verwendung mit String
$url = MFormOutputHelper::getCustomLinkUrl('redaxo://123');

// Mit Link-Array
$url = MFormOutputHelper::getCustomLinkUrl([
    'link' => 'redaxo://123'
]);

// Mit vorbereitetem Link-Array
$url = MFormOutputHelper::getCustomLinkUrl([
    'customlink_url' => '/mein-artikel'
]);
```

Die Methode ist besonders nützlich, wenn Sie nur die URL benötigen und keine weiteren Link-Informationen. Sie verarbeitet automatisch verschiedene Eingabeformate und gibt immer die korrekte URL zurück.

#### Mögliche Array-Werte

Ein vollständiges Link-Array kann folgende Werte enthalten:

```php
[
    // Basis-Werte
    'link' => '',               // Original Link-Wert (z.B. 'redaxo://123', 'mailto:mail@domain.com')
    'text' => '',              // Optionaler benutzerdefinierter Link-Text
    
    // Generierte Link-Informationen
    'customlink_text' => '',    // Automatisch generierter Link-Text
    'customlink_url' => '',     // Die verarbeitete URL
    'customlink_target' => '',  // Target-Attribut (z.B. ' target="_blank" rel="noopener noreferrer"')
    'customlink_class' => '',   // CSS-Klasse (z.B. ' internal', ' external', ' media', ' mail', ' tel')
    
    // Link-Typ und IDs
    'type' => '',              // Art des Links: 'internal', 'external', 'media', 'email', 'telephone'
    'article_id' => null,      // ID des Artikels (bei internen Links)
    'clang_id' => null,        // Sprach-ID
    
    // Medien-spezifische Informationen
    'filename' => null,        // Dateiname bei Media-Links
    'extension' => null,       // Dateierweiterung bei Media-Links
    
    // URL-Komponenten
    'protocol' => null,        // Protokoll bei externen Links (z.B. 'http', 'https')
    'domain' => null,         // Domain bei externen Links
    
    // Zusätzliche Metadaten
    'metadata' => [
        // Für Artikel
        'article_name' => '',  // Name des Artikels
        'template_id' => '',   // Template-ID
        'priority' => '',      // Priorität
        'parent_id' => '',     // Parent-ID
        'category_id' => '',   // Kategorie-ID
        'createdate' => '',    // Erstellungsdatum
        'updatedate' => '',    // Aktualisierungsdatum
        
        // Für Medien
        'title' => '',        // Medientitel
        'filesize' => '',     // Dateigröße
        'width' => '',        // Breite (bei Bildern)
        'height' => '',       // Höhe (bei Bildern)
        'mimetype' => '',     // MIME-Typ
        
        // Für E-Mail/Telefon
        'email' => '',        // E-Mail-Adresse bei mailto: Links
        'phone_number' => '', // Telefonnummer bei tel: Links
    ]
]
```

## Auslesen der YLinks

Analog zur vorherigen Version mit dem neuen Namespace:

```php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

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
