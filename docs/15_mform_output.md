# MFormOutput – Fluent Module Output

`MFormOutput` ist ein chainable Wrapper um Repeater-Daten, mit dem Modul-Outputs in wenigen Zeilen geschrieben werden können. Die Klasse baut auf `MFormRepeaterHelper::decode()` auf, filtert deaktivierte Items automatisch und entfernt interne Schlüssel.

```php
use FriendsOfRedaxo\MForm\Output\MFormOutput;

echo MFormOutput::from('REX_VALUE[1]')
    ->filter('active', '1')
    ->sort('position')
    ->limit(6)
    ->whenEmpty('<p>Keine Einträge.</p>')
    ->renderGrid(3, fn($item) => '<h3>' . rex_escape($item['title']) . '</h3>');
```

## Erstellen

| Methode | Zweck |
|---|---|
| `MFormOutput::from(string\|array $source)` | Aus REX_VALUE-String oder Array |
| `MFormOutput::empty()` | Leere Instanz |

## Filtern, Sortieren, Limitieren (chainable)

| Methode | Beschreibung |
|---|---|
| `->filter(string $field, mixed $value, bool $strict = false)` | Items mit passendem Feldwert behalten |
| `->where(callable $cb)` | Eigene Filter-Logik (`fn($item, $i) => bool`) |
| `->sort(string $field, string $direction = 'asc')` | Sortieren nach Feld (asc/desc) |
| `->reverse()` | Reihenfolge umkehren |
| `->limit(int $limit, int $offset = 0)` | Auf N Items begrenzen |
| `->skip(int $offset)` | Erste N überspringen |
| `->page(int $page, int $perPage)` | Eine Seite (1-basiert) |
| `->map(callable $cb)` | Items transformieren (`fn($item, $i) => array`) |

Alle Methoden geben eine **neue** Instanz zurück (immutable) – die Originaldaten werden nicht verändert.

## Inspizieren (terminal)

| Methode | Rückgabe |
|---|---|
| `->all()` | `array<int, array>` |
| `->first()` | `?array` |
| `->last()` | `?array` |
| `->count()` | `int` |
| `->isEmpty()` | `bool` |
| `->pluck(string $field)` | `array` (eine Spalte) |
| `->group(string $field)` | `array<string, array>` (gruppiert) |

## Rendern

### `render(callable $template): string`

Einfaches Concat-Rendering pro Item:

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->render(fn($item) => '<li>' . rex_escape($item['name']) . '</li>');
```

### `renderList(callable $template, ...): string`

Mit umschließendem `<ul>` / `<ol>`. Beide `listAttrs` und `itemAttrs` akzeptieren beliebige HTML-Attribute (inkl. boolean wie `disabled`, `uk-tab`):

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderList(
        fn($item) => rex_escape($item['title']),
        listTag: 'ul',
        itemTag: 'li',
        listAttrs: ['class' => 'nav nav-pills', 'role' => 'tablist'],
        itemAttrs: ['class' => 'nav-item'],
    );
```

`class` darf auch als Array übergeben werden:

```php
listAttrs: ['class' => ['uk-list', 'uk-list-divider']]
```

### `renderGrid(int $cols, callable $template, string $framework = 'bootstrap', array $rowAttrs = [], array $colAttrs = [], string $rowTag = 'div', string $colTag = 'div'): string`

Framework-aware Grid-Output. Unterstützt **Bootstrap**, **Tailwind**, **UIKit** und **none** (ohne Preset). Eigene Klassen/Attribute werden in das Preset hineingemerged — bei `class` wird konkateniert, alle anderen Attribute überschreiben.

| Framework | Row-Klassen | Spalten-Klassen | Extra |
|---|---|---|---|
| `bootstrap` (default) | `row` | `col-12 col-md-{12/N}` | – |
| `tailwind` | `grid grid-cols-1 md:grid-cols-{N} gap-4` | – | – |
| `uikit` | `uk-grid-match uk-child-width-1-{N}@m` | – | `uk-grid` Attribut |
| `none` | – | – | komplett selbst über `$rowAttrs`/`$colAttrs` |

**Bootstrap (Default):**
```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(3, fn($i) => '<div class="card">'.rex_escape($i['title']).'</div>');
```

**Tailwind:**
```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(3, fn($i) => '<div class="rounded p-4 bg-gray-50">'.rex_escape($i['title']).'</div>',
        framework: 'tailwind');
```

**UIKit:**
```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(4, fn($i) => '<div class="uk-card uk-card-default uk-card-body">'.rex_escape($i['title']).'</div>',
        framework: 'uikit');
```

**Eigene Klassen ergänzen** (Default-Klassen bleiben erhalten):
```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(2, fn($i) => '<h3>'.rex_escape($i['title']).'</h3>',
        framework: 'tailwind',
        rowAttrs: ['class' => 'gap-8 my-12'],         // wird zu "grid grid-cols-1 md:grid-cols-2 gap-4 gap-8 my-12"
        colAttrs: ['class' => 'shadow-lg', 'data-aos' => 'fade-up']);
```

**Komplett eigene Struktur** mit `framework: 'none'`:
```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(3, fn($i) => '<article>'.rex_escape($i['title']).'</article>',
        framework: 'none',
        rowTag: 'section',
        rowAttrs: ['class' => 'my-grid', 'role' => 'list'],
        colTag: 'article',
        colAttrs: ['class' => 'my-cell']);
```

**Default-Framework projektweit setzen** (z. B. in `boot.php`):
```php
\FriendsOfRedaxo\MForm\Output\MFormOutput::setDefaultGridFramework(
    \FriendsOfRedaxo\MForm\Output\MFormOutput::GRID_TAILWIND
);
```

### `renderChunks(int $size, callable $template): string`

Liefert pro Chunk einen Array – flexibler als `renderGrid` z. B. für Slider:

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderChunks(4, function (array $chunk, int $i) {
        $html = '<div class="slide" data-index="' . $i . '">';
        foreach ($chunk as $item) {
            $html .= '<div>' . rex_escape($item['title']) . '</div>';
        }
        return $html . '</div>';
    });
```

### `renderFragment(string $name, array $data = []): string`

Übergibt `$items` und `$data` an ein REDAXO-Fragment:

```php
// fragments/teaser.php hat Zugriff auf $this->getVar('items') und $this->getVar('data')
echo MFormOutput::from('REX_VALUE[1]')
    ->sort('position')
    ->renderFragment('teaser.php', ['heading' => 'Aktuelles']);
```

## Fallback bei leeren Daten

`whenEmpty()` setzt einen String oder Callback, der von **allen** `render*`-Methoden ausgegeben wird, sobald keine Items übrig sind:

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->filter('active', '1')
    ->whenEmpty(fn() => '<div class="alert alert-info">Bald mehr…</div>')
    ->render(fn($i) => '<p>' . rex_escape($i['text']) . '</p>');
```

## Praktische Pattern

### Pagination via GET-Parameter

```php
$page = rex_request('page', 'int', 1);

echo MFormOutput::from('REX_VALUE[1]')
    ->sort('date', 'desc')
    ->page($page, 10)
    ->render(fn($i) => '<article>' . rex_escape($i['headline']) . '</article>');
```

### Gruppierter Output

```php
$grouped = MFormOutput::from('REX_VALUE[1]')
    ->sort('name')
    ->group('category');

foreach ($grouped as $category => $items) {
    echo '<h2>' . rex_escape($category) . '</h2>';
    echo MFormOutput::from($items)->render(
        fn($i) => '<li>' . rex_escape($i['name']) . '</li>'
    );
}
```

### Chained Filter + Map

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->where(fn($i) => !empty($i['image']))
    ->map(fn($i) => $i + ['url' => rex_media::get($i['image'])?->getUrl()])
    ->renderGrid(2, fn($i) => '<img src="' . rex_escape($i['url']) . '">');
```

## HTML-Tag-Helper

Inspiriert von [forhtml](https://github.com/FriendsOfREDAXO/forhtml) gibt es eine statische `tag()`-Methode für sauberes HTML in Templates:

```php
use FriendsOfRedaxo\MForm\Output\MFormOutput;

echo MFormOutput::tag('a',
    ['href' => $url, 'class' => ['btn', 'btn-primary'], 'target' => '_blank'],
    'Mehr erfahren'
);
// → <a href="..." class="btn btn-primary" target="_blank">Mehr erfahren</a>

// Boolean-Attribute (HTML5):
echo MFormOutput::tag('button',
    ['class' => 'btn', 'disabled' => true],
    'Senden'
);
// → <button class="btn" disabled>Senden</button>

// Void-Tags werden auto-closed:
echo MFormOutput::tag('img', ['src' => $src, 'alt' => $alt]);
// → <img src="..." alt="...">
```

Praktisch im Template-Callback:

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(3, fn($i) => MFormOutput::tag('article',
        ['class' => 'card', 'data-id' => $i['id'] ?? null],
        MFormOutput::tag('h3', [], rex_escape($i['title'])) .
        MFormOutput::tag('p', ['class' => 'lead'], rex_escape($i['lead']))
    ), framework: 'tailwind');
```

## Sicherheit

`MFormOutput` führt **keinen** Escape auf Item-Werten durch — nur auf Tag-Attributen, die du selbst übergibst (`listAttrs`, Klassen). Im Template-Callback bist **du** für `rex_escape()` zuständig. Das gilt auch für `renderFragment()`.

## Single-Value Helfer

Neben dem fluenten Repeater-Wrapper bietet `MFormOutput` statische Helfer für klassische `REX_VALUE[…]`-Felder. Sie enthalten echte Logik (Parsing, Mapping, Word-Splitting) und sind **kein** 1:1-Wrapper für Core-Methoden.

### `link()` und `linkUrl()`

Auflösung gespeicherter Link-Werte aus `addCustomLinkField` / `addLinkField` / YForm `custom_link`:

| Eingabe | Verhalten |
|---|---|
| `rex-article://12` | `rex_getUrl(12)` |
| `rex-article://12-2` | `rex_getUrl(12, 2)` (mit clang) |
| `rex-media://foo.jpg` | `rex_url::media('foo.jpg')` |
| `tel:`, `mailto:` | unverändert |
| `https://…` | unverändert, externer Link bekommt automatisch `target="_blank"` + `rel="noopener"` |
| `42` (numerisch) | `rex_getUrl(42)` |

```php
// Nur die URL:
$url = MFormOutput::linkUrl('REX_VALUE[3]');

// Fertiges <a>:
echo MFormOutput::link('REX_VALUE[3]', 'Mehr erfahren', ['class' => 'btn btn-primary']);

// Linktext leer → Artikeltitel bzw. URL als Fallback
echo MFormOutput::link('rex-article://12');
```

### `picture()`

Erzeugt ein vollständiges `<picture>` aus einer Media-Query → media_manager-Type-Map:

```php
echo MFormOutput::picture('REX_MEDIA[1]', [
    '(min-width: 1200px)' => 'hero_desktop',
    '(min-width: 768px)'  => 'hero_tablet',
    '(max-width: 767px)'  => 'hero_mobile',
], ['class' => 'w-100', 'loading' => 'lazy']);
```

Der `<img>`-Fallback nutzt den letzten Type als `src`. Das `alt`-Attribut wird aus den Mediapool-Metadaten (`title`) übernommen, kann aber via `$imgAttrs['alt']` überschrieben werden.

### `mediaList()`

Splittet einen CSV-Wert von `addMedialistField` / `addImagelistField` und liefert nur tatsächlich existierende `rex_media`-Instanzen:

```php
foreach (MFormOutput::mediaList('REX_VALUE[5]') as $media) {
    echo MFormOutput::tag('img', [
        'src' => rex_url::media($media->getFileName()),
        'alt' => $media->getValue('title') ?? '',
    ]);
}
```

### `richtext()`

Markiert TinyMCE/WYSIWYG-Output als bereits-HTML (kein doppeltes Escape) und filtert optional die erlaubten Tags:

```php
echo MFormOutput::richtext('REX_VALUE[2]');
// optional: nur bestimmte Tags zulassen
echo MFormOutput::richtext('REX_VALUE[2]', ['p', 'br', 'strong', 'em', 'a', 'ul', 'li']);
```

### `excerpt()`

Plaintext-Auszug aus HTML, getrimmt auf N Wörter (collapsed Whitespace, decodierte Entities):

```php
$teaser = MFormOutput::excerpt('REX_VALUE[2]', 30);
echo MFormOutput::tag('p', ['class' => 'text-muted'], rex_escape($teaser));
```

## Beispiel-Modul

Im Backend unter **Demos → Repeater** steht das Beispiel **„Content-Pflege-Modul [Output via MFormOutput]"** zur Installation bereit. Es zeigt einen typischen redaktionellen Workflow: Headline + Intro als Single-Values plus einen Repeater mit Titel, TinyMCE-Text, Hauptbild, Link und Galerie — komplett über `MFormOutput::from()->renderGrid()` und die Single-Value-Helfer (`picture`, `link`, `richtext`, `excerpt`, `mediaList`) ausgegeben.
