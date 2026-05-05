
# Media- & Link-Elemente

MForm stellt Felder für Medien und Links bereit. Dabei gibt es zwei Kategorien:

| Methode | Widget | Speicherformat |
|---|---|---|
| `addMediaField()` | REDAXO Core (`rex_var_media`) | Dateiname (`REX_MEDIA`) |
| `addLinkField()` | REDAXO Core (`rex_var_link`) | Artikel-ID (`REX_LINK`) |
| `addMedialistField()` | **MForm-Widget** (`rex_var_custom_medialist`) | Kommagetrennte Dateinamen (`REX_MEDIALIST`) |
| `addLinklistField()` | **MForm-Widget** (`rex_var_custom_linklist`) | Kommagetrennte Artikel-IDs (`REX_LINKLIST`) |
| `addImagelistField()` | REDAXO Core (`rex_var_imglist`) | Kommagetrennte Dateinamen |
| `addCustomLinkField()` | **MForm-Widget** | Link-String (`REX_VALUE`) |
| `addCustomLinkMultipleField()` | **MForm-Widget** | JSON-Array von Link-Strings (`REX_VALUE`) |

> **Hinweis:** `addMedialistField()` und `addLinklistField()` wurden in Version 9 durch eigene MForm-Widgets ersetzt.  
> Diese bieten ein modernes Listen-UI mit Drag-and-Drop-Sortierung und sind vollständig Repeater-kompatibel.  
> Das Speicherformat bleibt identisch zum nativen REDAXO-Format – bestehende Module funktionieren ohne Änderung weiter.

## Modul-Eingabe

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addFieldsetArea('Medien', MForm::factory()
        // natives REDAXO Core-Widget
        ->addMediaField(1, ['label' => 'Einzelbild'])
        // MForm eigenes Listen-Widget (Repeater-kompatibel)
        ->addMedialistField(2, ['label' => 'Bildliste'])
        // wie addMedialistField, optimiert für reine Bildlisten
        ->addImagelistField(3, ['label' => 'Imagelist'])
    )
    ->addFieldsetArea('Links', MForm::factory()
        // natives REDAXO Core-Widget
        ->addLinkField(1, ['label' => 'Link'])
        // MForm eigenes Listen-Widget (Repeater-kompatibel)
        ->addLinklistField(2, ['label' => 'Linkliste'])
        // Custom-Link: intern, extern, Media, Mailto, Tel in einem Feld
        ->addCustomLinkField(3, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
        // Custom-Link-Multi: mehrere Links, gespeichert als JSON-Array
        ->addCustomLinkMultipleField(4, ['label' => 'Mehrere Links', 'btn_add' => 'Link hinzufügen'])
    )
    ->show();
```

## Modul-Ausgabe

```php
<?php
// Einzelmedium
$media = rex_media::get('REX_MEDIA[id=1]');

// Medialist – kommagetrennte Dateinamen
$mediaList = array_filter(explode(',', 'REX_MEDIALIST[id=2]'));

// Link – Artikel-ID
$article = rex_article::get((int) 'REX_LINK[id=1]');

// Linklist – kommagetrennte Artikel-IDs
$linkList = array_filter(explode(',', 'REX_LINKLIST[id=2]'));

// Custom Link
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
$url = MFormOutputHelper::getCustomUrl('REX_VALUE[id=3]');

// Custom Link Multi – JSON-Array dekodieren
$rawLinks = html_entity_decode('REX_VALUE[id=4]', ENT_QUOTES | ENT_HTML5, 'UTF-8');
$links = json_decode($rawLinks, true) ?? [];
foreach ($links as $link) {
    $url = MFormOutputHelper::getCustomUrl($link);
}
```

## Parameter

### `addMediaField` / `addMedialistField`

```php
->addMediaField(1, [
    'label'    => 'Bild',
    'types'    => 'png,jpg,svg',  // Dateitypen filtern
    'preview'  => 1,              // Vorschau anzeigen
    'category' => 2,              // Medienkategorie vorauswählen
])

->addMedialistField(2, [
    'label'    => 'Bildliste',
    'types'    => 'gif,jpg',
    'preview'  => 1,
    'category' => 4,
    'view'     => 'grid', // Startansicht: list|grid
    'view_switch' => 1,   // Toggle-Button für Listen-/Rasteransicht anzeigen
])
```

#### View-Switch beim `addMedialistField`

Der View-Switch gilt nur für das MForm-Medialist-Widget (`addMedialistField`).

- `view`: Startansicht des Widgets (`list` oder `grid`, Standard: `list`)
- `view_switch`: Schaltet den Toggle-Button in der Toolbar ein/aus (Standard: `1`)

Beispiel ohne Umschalt-Button (feste Rasteransicht):

```php
->addMedialistField(2, [
    'label'       => 'Bildliste',
    'view'        => 'grid',
    'view_switch' => 0,
])
```

Hinweis:

- Die gewählte Ansicht wird pro Widget im Browser gespeichert und beim nächsten Laden wiederhergestellt.
- Das Speicherformat der Werte bleibt unverändert (`REX_MEDIALIST`, kommagetrennte Dateinamen).

#### Preview-Verhalten beim `addMedialistField`

Die Medialist-Vorschau orientiert sich am Verhalten der `imagelist`:

- Bilddateien (`jpg`, `jpeg`, `png`, `gif`, `webp`, `svg`, `avif`) werden als Thumbnail gerendert.
- Nicht-Bilddateien werden als Dateityp-Badge angezeigt (z. B. `PDF`).
- Die Preview-URL wird intern über `rex_medialistbutton_preview` erzeugt und korrekt entity-dekodiert,
  damit keine ungültigen `&amp;`-URLs im `<img src>` landen.

Hinweis fuer bestehende Installationen:

- Wenn nach einem Update noch keine Thumbnails erscheinen, Backend-Assets einmal hart neu laden (Cache leeren).

### `addLinkField` / `addLinklistField`

```php
->addLinkField(1, ['label' => 'Link', 'category' => 3])
->addLinklistField(2, ['label' => 'Linkliste', 'category' => 2])
```

Alternativ über `setParameter()`:

```php
->addMediaField(3)
    ->setLabel('Bild')
    ->setParameter('preview', 1)
    ->setParameter('category', 2)
    ->setParameter('types', 'png')

->addLinklistField(4)
    ->setLabel('Linkliste')
    ->setParameters(['label' => 'Linkliste', 'category' => 2])
```

### `addCustomLinkField` – alle Typen

```php
->addCustomLinkField(1, [
    'label'      => 'Link',
    'data-intern'  => 'enable',
    'data-extern'  => 'enable',
    'data-media'   => 'enable',
    'data-mailto'  => 'enable',
    'data-tel'     => 'enable',
    'anchor'       => 0,   // Anker-Button ausblenden
])
```

Ylink (eigene Tabellen als Link-Quelle):

```php
$ylink = [['name' => 'Länder', 'table' => 'rex_ycountries', 'column' => 'de_de']];
->addCustomLinkField(1, ['label' => 'Custom', 'data-intern' => 'disable', 'data-extern' => 'enable', 'ylink' => $ylink])
```

### Als REX_VAR

```html
REX_CUSTOM_LINK[id=5 widget=1 external=1 intern=0 mailto=0 phone=1 media=1 ylink="Countries::rex_ycountries::de_de"]
```

