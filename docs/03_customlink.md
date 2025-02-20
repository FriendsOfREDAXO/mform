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

## Auslesen der YLinks per Outputfilter

### YForm Custom-Link-Value

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

### Auslesen der `ylink`-Werte manuell

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

### Custom-Link-Werte aus MBlock zum Repeater (MForm >=8) konvertieren

Das Datenformat der CustomLinks im Repeater unterscheidet sich von MBlock. Der nachfolgende Konverter hilft bei der Umstellung. Kopiere den Code direkt in das betroffene Modul und passe ihn an, oder führe den Code separat aus.

> **Hinweis: Nicht vergessen, ein Backup zu machen, um im Fall eines Fehlers eine Wiederherstellung zu ermöglichen.** Insb. ein Backup der Tabelle `rex_article_slice` und ggf. `rex_article`, damit diese bei einer Wiederherstellung zusammenpassen.

Es muss das Value angepasst werden in dem gesucht werden soll, die Bezeichnung des Feldes und die Modul-ID.

```php
<?php
// Define the parameters
$column = 'value1'; // column: value1, ..., value10
$node = 'customlink'; //  node
$moduleId = 48; // module_id

// Fetch all records from the rex_article_slice table where module_id is the specified value
$sql = rex_sql::factory();
$sql->setQuery("SELECT id, $column FROM rex_article_slice WHERE module_id = ?", [$moduleId]);

foreach ($sql as $row) {
    $id = $row->getValue('id');
    $jsonData = $row->getValue($column);

    // Decode JSON data
    $data = json_decode($jsonData, true);

    // Check if decoding was successful and if the data is an array
    if (json_last_error() === JSON_ERROR_NONE && is_array($data)) {
        $modified = false;

        // Traverse the JSON array and modify customlink nodes
        foreach ($data as &$item) {
            if (isset($item[$node])) {
                $value = $item[$node];
                
                // Check if the value is already in the desired format
                $isNewFormat = is_array($value) && isset($value['name']) && isset($value['id']);

                if (!$isNewFormat) {
                    if (is_numeric($value)) {

                        $articleId = (int)$value;
    
                        // Artikelobjekt laden
                        $article = rex_article::get($articleId);
                        $articleName = "article not found: redaxo://$value";
                        if ($article) {
                            // Artikelname abrufen
                            $articleName = rex_escape($article->getName());
                        }

                        $item[$node] = [
                            'name' => "$articleName",
                            'id' => "redaxo://$value"
                        ];
                    } else {
                        $item[$node] = [
                            'name' => "$value",
                            'id' => "$value"
                        ];
                    }
                    $modified = true;
                }
            }
        }

        // If data was modified, update the database
        if ($modified) {
            $updatedJsonData = json_encode($data);
            $updateSql = rex_sql::factory();
            $updateSql->setTable('rex_article_slice');
            $updateSql->setWhere(['id' => $id]);
            $updateSql->setValue($column, $updatedJsonData);
            $updateSql->update();
            echo "Updated record ID $id<br>";
        }
    } else {
        // Log or handle invalid JSON data if necessary
        echo "Invalid JSON data in record ID $id<br>";
    }
}

echo "Conversion completed.<br>";
?>

<?php

echo '<pre>';
print_r(rex_var::toArray("REX_VALUE[1]"));
echo '</pre>';

?>
```
