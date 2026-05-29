
# Media- & Link-Elemente

MForm stellt Widgets fuer Medien und Links in mehreren REDAXO-Kontexten bereit:

- klassische Module
- `rex_form`
- YForm
- optional auch direkt als `REX_VAR`

## Verfuegbarkeit nach Kontext

| Widget | Klassisches Modul | `rex_form` | YForm | `REX_VAR` | Speicherformat |
|---|---|---|---|---|---|
| `custom_medialist` | ja | ja | ja | ja | kommagetrennte Dateinamen |
| `imagelist` | ja | ja | ja | ja (`REX_IMGLIST`) | kommagetrennte Dateinamen |
| `custom_linklist` | ja | ja | ja | ja | kommagetrennte Artikel-IDs |
| `custom_link` | ja | ja | ja | ja | einzelner Link-String |
| `custom_link_multi` | ja | ja | ja | ja | JSON-Array von Link-Strings |
| `mform-media` _(v9+)_ | ja | – | – | – | Dateiname in `REX_VALUE` |
| `mform-link` _(v9+)_ | ja | – | – | – | Link-String in `REX_VALUE` |

> **Wichtig:** Alle hier aufgefuehrten MForm-Widgets sind jetzt auch in YForm abbildbar.
> Dabei nutzen `medialist` und `linklist` eigene YForm-Value-Types, die auf den MForm-Widgets basieren.

## MForm-Methoden und technische Basis

| Methode | Technische Basis | Speicherformat |
|---|---|---|
| `addMediaField()` | REDAXO Core (`rex_var_media`) | Dateiname (`REX_MEDIA`) |
| `addLinkField()` | REDAXO Core (`rex_var_link`) | Artikel-ID (`REX_LINK`) |
| `addMFormMediaField()` | MForm-Widget (`rex_var_custom_link`) | Dateiname (`REX_VALUE`) |
| `addMFormLinkField()` | MForm-Widget (`rex_var_custom_link`) | Link-String (`REX_VALUE`) |
| `addMedialistField()` | MForm-Widget (`rex_var_custom_medialist`) | kommagetrennte Dateinamen (`REX_MEDIALIST`) |
| `addLinklistField()` | MForm-Widget (`rex_var_custom_linklist`) | kommagetrennte Artikel-IDs (`REX_LINKLIST`) |
| `addImagelistField()` | MForm-Widget (`rex_var_imglist`) | kommagetrennte Dateinamen |
| `addCustomLinkField()` | MForm-Widget (`rex_var_custom_link`) | Link-String (`REX_VALUE`) |
| `addCustomLinkMultipleField()` | MForm-Widget (`rex_var_custom_link_multi`) | JSON-Array von Link-Strings (`REX_VALUE`) |

> **Hinweis:** `addMedialistField()` und `addLinklistField()` wurden in Version 9 durch eigene MForm-Widgets ersetzt.
> Diese bieten ein modernes Listen-UI mit Drag-and-Drop-Sortierung und sind voll repeater-kompatibel.
> Das Speicherformat bleibt identisch zum nativen REDAXO-Format.
>
> **MBlock-Praxis:** Die klassischen REDAXO-Varianten von `linklist`/`medialist` waren im MBlock-Kontext historisch nie stabil.
> Wenn du die Funktion in MBlock brauchst, sollten die MForm-Custom-Widgets verwendet werden.
> Bonus mit MForm 9+: `MForm::useCustomLinkForClassicWidgets(true)` kann auch klassische Felder intern auf die stabileren MForm-Widgets legen.

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
        // Unified Media-Wrapper (MForm v9+): speichert in REX_VALUE, mit Typ-Filter und Media-Vorschau
        ->addMFormMediaField(4, ['types' => 'jpg,png,webp', 'preview' => '1'], null, ['label' => 'MForm Media'])
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
        // Unified Link-Wrapper (MForm v9+): speichert in REX_VALUE, unterstützt Typ-Einschränkung
        ->addMFormLinkField(5, ['data-intern' => 'enable', 'data-extern' => 'enable'], null, ['label' => 'MForm Link'])
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

// MFormLink / MFormMedia – Wert liegt in REX_VALUE (wie custom_link)
$mformLinkUrl = MFormOutputHelper::getCustomUrl('REX_VALUE[id=5]');
$mformMediaFilename = 'REX_VALUE[id=4]'; // direkt als Dateiname verwendbar
$media = rex_media::get($mformMediaFilename);
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
    'category' => 4,
    'view'     => 'gallery', // Startansicht: list|grid|gallery
    'views'    => 'gallery,grid,list',
    'view_switch' => 1,      // Toggle-Button für die Ansichten anzeigen
])
```

#### View-Switch beim `addMedialistField`

Der View-Switch gilt nur für das MForm-Medialist-Widget (`addMedialistField`).

- `view`: Startansicht des Widgets (`list`, `grid` oder `gallery`, Standard: `list`)
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

### `addMFormMediaField` – Unified Media-Wrapper (MForm v9+)

Basiert intern auf `custom_link` und speichert den Dateinamen in `REX_VALUE[n]`.
Gegenüber `addMediaField()` (Core-Widget) bietet es:

- Typ-Einschränkung per `types`-Parameter
- optionaler Vorschau-Button für Bild- und Videodateien
- vollständige MBlock/Repeater-Kompatibilität (korrektes Reindex und Clone-Reset)

```php
->addMFormMediaField("$id.0.image", [
    'preview' => '1',            // Vorschau-Button für Bilder/Videos anzeigen
    'types'   => 'jpg,png,webp', // nur diese Dateitypen auswählbar
], null, [
    'label' => 'Bild',
])

// Modul-Ausgabe (Wert liegt in REX_VALUE, nicht REX_MEDIA):
$filename = 'REX_VALUE[id=1]';
$media = rex_media::get($filename);
```

> **Hinweis:** `addMediaField()` speichert in `REX_MEDIA[n]`, `addMFormMediaField()` in `REX_VALUE[n]`.
> Nicht nachträglich im gleichen Slot austauschen – gespeicherte Werte sind inkompatibel.

### `addMFormLinkField` – Unified Link-Wrapper (MForm v9+)

Basiert intern auf `custom_link` und speichert den Linkwert in `REX_VALUE[n]`.
Gegenüber `addLinkField()` (Core-Widget) unterstützt es alle Link-Typen des Custom-Link-Widgets.

```php
->addMFormLinkField("$id.0.link", [
    'data-intern'  => 'enable',
    'data-extern'  => 'enable',
    'data-media'   => 'disable',
    'data-mailto'  => 'disable',
    'data-tel'     => 'disable',
], null, [
    'label' => 'Link',
])

// Modul-Ausgabe (Wert ist ein custom_link-kompatibler Link-String):
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;
$url = MFormOutputHelper::getCustomUrl('REX_VALUE[id=1]');
```

> **Hinweis:** `addLinkField()` speichert eine Artikel-ID in `REX_LINK[n]`, `addMFormLinkField()` speichert
> einen Link-String (intern/extern/media/mailto/tel) in `REX_VALUE[n]`.
> Nicht nachträglich im gleichen Slot austauschen.

## Empfehlungen je Kontext

- Klassische Module: bevorzugt direkte PHP-Widgets oder MForm-Methoden verwenden.
- `rex_form`: die mitgelieferten `form_element`-Klassen verwenden.
- YForm: nur die vorhandenen Value-Types einsetzen.
- `REX_VAR`: weiter unterstuetzt, aber primaer fuer modulnahe REDAXO-Syntax gedacht.

## Key-Konventionen fuer MBlock und Repeater

Media- und Link-Felder verhalten sich in MBlock und im Flex-Repeater **unterschiedlich**, je nachdem ob eine numerische oder String-basierte ID verwendet wird.

| Kontext | Methode | ID-Typ | Ausgabe-Schlüssel im `$item`-Array |
|---------|---------|--------|-------------------------------------|
| MBlock | `addMediaField(1)` | numerisch (Pflicht!) | `$item['REX_MEDIA_1']` |
| MBlock | `addLinkField(2)` | numerisch (Pflicht!) | `$item['REX_LINK_2']` |
| MBlock | `addMedialistField(3)` | numerisch (Pflicht!) | `$item['REX_MEDIALIST_3']` |
| MBlock | `addLinklistField(4)` | numerisch (Pflicht!) | `$item['REX_LINKLIST_4']` |
| MBlock | `addCustomLinkField("$id.0.link")` | String-Pfad (empfohlen) | `$item['link']` |
| MBlock | `addMFormMediaField("$id.0.bild")` | String-Pfad (empfohlen) | `$item['bild']` |
| Repeater | `addMediaField("bild")` | String (Pflicht!) | `$item['bild']` |
| Repeater | `addLinkField("link")` | String (Pflicht!) | `$item['link']` |
| Repeater | `addCustomLinkField("link")` | String (empfohlen) | `$item['link']` |

> **Wichtig:** `addMediaField()` und `addLinkField()` im **MBlock**-Kontext erfordern eine **numerische** ID.
> MBlock leitet daraus intern den REDAXO-Variablenname `REX_MEDIA_n` oder `REX_LINK_n` ab.
> Im **Flex-Repeater**-Kontext gibt es kein `REX_MEDIA_n`-Konzept – hier muss die ID ein lesbarer **String-Key** sein, der direkt als JSON-Schlüssel verwendet wird.

## Verweise auf die Detaildokumentation

- `custom_link` und `custom_link_multi`: siehe `03_customlink.md`
- `imagelist`, `custom_medialist` und `custom_linklist`: siehe `04_imagelist.md`

