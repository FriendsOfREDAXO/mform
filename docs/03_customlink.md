# Custom-Link-Widget

Das Custom-Link-Widget bündelt mehrere Link-Typen in einem Feld.

## Verfügbarkeit

| Methode | Klassisches Modul | Flex-Repeater | `rex_form` | YForm | `REX_VAR` |
|---|---|---|---|---|---|
| `addCustomLinkField` | ja | ja | ja | ja | ja |
| `addCustomLinkMultipleField` | ja | ja | ja | ja | ja |

## Klassisches Modul

### Mit MForm

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addFieldsetArea('Links', MForm::factory()
        ->addCustomLinkField(1, [
            'label' => 'Custom Link',
            'data-intern' => 'enable',
            'data-extern' => 'enable',
            'data-media' => 'enable',
            'data-mailto' => 'enable',
            'data-tel' => 'enable',
        ])
        ->addCustomLinkMultipleField(2, [
            'label' => 'Mehrere Links',
            'btn_add' => 'Link hinzufuegen',
            'data-intern' => 'enable',
            'data-extern' => 'enable',
            'data-media' => 'enable',
            'data-mailto' => 'enable',
            'data-tel' => 'enable',
        ])
    )
    ->show();
```

### Direkt als PHP-Widget

```php
<?php
echo rex_var_custom_link::getWidget(
    '1',
    'REX_INPUT_VALUE[1]',
    'REX_VALUE[1]',
    [
        'intern' => 1,
        'external' => 1,
        'media' => 1,
        'mailto' => 1,
        'phone' => 1,
        'anchor' => 1,
    ]
);

echo rex_var_custom_link_multi::getWidget(
    '2',
    'REX_INPUT_VALUE[2]',
    'REX_VALUE[2]',
    [
        'intern' => 1,
        'external' => 1,
        'media' => 1,
        'mailto' => 1,
        'phone' => 1,
        'anchor' => 1,
        'btn_add' => 'Link hinzufuegen',
    ]
);
```

### Modul-Ausgabe

```php
<?php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$value = 'REX_VALUE[id=1]';
$url = MFormOutputHelper::getCustomUrl($value);
$data = MFormOutputHelper::prepareCustomLink(['link' => $value], true);

if ($url) {
    echo '<a href="' . rex_escape($url) . '"' . $data['customlink_target'] . '>'
        . rex_escape($data['customlink_text'])
        . '</a>';
}
```

Bei `custom_link_multi` muss der gespeicherte JSON-String zuerst dekodiert werden:

```php
<?php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$rawValue = html_entity_decode('REX_VALUE[id=2]', ENT_QUOTES | ENT_HTML5, 'UTF-8');
$links = json_decode($rawValue, true) ?? [];

foreach ($links as $link) {
    $url = MFormOutputHelper::getCustomUrl($link);
    $data = MFormOutputHelper::prepareCustomLink(['link' => $link], true);
    echo '<a href="' . rex_escape($url) . '"' . $data['customlink_target'] . '>'
        . rex_escape($data['customlink_text'])
        . '</a>';
}
```

## rex_form

### `custom_link`

```php
<?php
$field = $form->addField('', 'link', null, ['internal::fieldClass' => 'rex_form_widget_mform_customlink_element'], true);
$field->setIntern(1);
$field->setExternal(1);
$field->setMedia(1);
$field->setMailto(1);
$field->setPhone(1);
$field->setCategoryId(0);
```

### `custom_link_multi`

```php
<?php
$field = $form->addField('', 'links', null, ['internal::fieldClass' => 'rex_form_widget_mform_custom_link_multi_element'], true);
$field->setIntern(1);
$field->setExternal(1);
$field->setMedia(1);
$field->setMailto(1);
$field->setPhone(1);
$field->setAnchor(1);
$field->setBtnAdd('Link hinzufuegen');
```

## YForm

Fuer YForm sind beide Varianten vorhanden:

- `custom_link`
- `custom_link_multi`

### `custom_link`

```php
<?php
$yform->setValueField('custom_link', [
    'name' => 'link',
    'label' => 'Link',
    'intern' => 1,
    'external' => 1,
    'media' => 1,
    'mailto' => 1,
    'phone' => 0,
    'anchor' => 1,
]);
```

### `custom_link_multi`

```php
<?php
$yform->setValueField('custom_link_multi', [
    'name' => 'links',
    'label' => 'Links',
    'intern' => 1,
    'external' => 1,
    'media' => 1,
    'mailto' => 1,
    'phone' => 0,
    'anchor' => 1,
    'btn_add' => 'Link hinzufuegen',
]);
```

## REX_VAR

### `custom_link`

```html
REX_CUSTOM_LINK[id=5 widget=1 external=1 intern=1 mailto=1 phone=1 media=1 anchor=1 ylink="Countries::rex_ycountries::de_de"]
```

### `custom_link_multi`

```html
REX_CUSTOM_LINK_MULTI[id=6 widget=1 external=1 intern=1 mailto=1 phone=0 media=1 anchor=1 btn_add="Link hinzufuegen"]
```

## Hinweise

- `custom_link` speichert einen einzelnen String, z. B. `redaxo://1` oder `mailto:test@example.org`.
- `custom_link_multi` speichert ein JSON-Array, z. B. `["redaxo://1","mailto:test@example.org"]`.
- Im MBlock-Kontext sollte `addCustomLinkField()` **immer mit einem String-Pfad** als ID verwendet werden (z. B. `"$id.0.link"`). Damit landet der Wert unter dem lesbaren Schlüssel `$item['link']` im JSON. Eine numerische ID funktioniert zwar technisch, ergibt aber den wenig hilfreichen Schlüssel `$item['6']`.

---

## Anchor-Link-Unterstützung

Das Custom-Link-Widget enthält einen **Anker-Button**, mit dem Redakteure direkt auf einen Slice/Artikel-Abschnitt verlinken können (setzt einen `#`-Fragment-Anker).

Standardmäßig ist der Button sichtbar. Er kann über das `anchor`-Attribut (Wert `0`) ausgeblendet werden:

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addFieldsetArea('Links', MForm::factory()
        // Standard: Anker-Button sichtbar
        ->addCustomLinkField(1, ['label' => 'Link mit Anker'])

        // Anker-Button ausblenden
        ->addCustomLinkField(2, ['label' => 'Link ohne Anker', 'anchor' => 0])
    )
    ->show();
```

---

## Auslesen der Custom-Links

MForm bietet mehrere Methoden zum Auslesen und Verarbeiten von Custom-Links.
Fuer neue Implementierungen wird der einheitliche Einstieg `createLinkData()` bzw. `normalizeLinkData()` empfohlen.

```php
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
```

### createLinkData() [NEU]

Einheitlicher Einstieg fuer Einzelwerte, Repeater-Werte und bereits vorbereitete Link-Arrays.

```php
$normalized = MFormOutputHelper::createLinkData($input);

// immer verfuegbar:
$url = $normalized['customlink_url'];
$text = $normalized['customlink_text'];
$target = $normalized['customlink_target'];
```

Unterstuetzte Eingaben:

- String, z. B. `redaxo://10`
- Array mit `link`
- Repeater-Format mit `id`/`name`
- Bereits vorbereitete Arrays mit `customlink_url`

### normalizeLinkData() [NEU]

Wie `createLinkData()`, aber mit Optionen:

```php
$normalized = MFormOutputHelper::normalizeLinkData($input, [
    'mode' => 'frontend',    // frontend|raw|strict
    'extern_blank' => true,
]);
```

### normalizeRepeaterItems() [NEU]

Normalisiert definierte Link-Felder innerhalb einer Repeater-Liste in einem Schritt.

```php
use FriendsOfRedaxo\MForm\Repeater\MFormRepeaterHelper;
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

$items = MFormRepeaterHelper::decode(1);

// fuegt pro Feld `<feldname>_normalized` hinzu
$items = MFormOutputHelper::normalizeRepeaterItems($items, ['link', 'cta']);

// alternativ originalfeld ersetzen:
// $items = MFormOutputHelper::normalizeRepeaterItems($items, ['link', 'cta'], ['replace' => true]);
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
