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

Mit umschließendem `<ul>` / `<ol>`:

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderList(
        fn($item) => rex_escape($item['title']),
        listTag: 'ul',
        itemTag: 'li',
        listAttrs: ['class' => 'nav nav-pills'],
    );
```

### `renderGrid(int $cols, callable $template, ...): string`

Bootstrap-konforme Grid-Ausgabe (`row` + `col-md-X`):

```php
echo MFormOutput::from('REX_VALUE[1]')
    ->renderGrid(3, function ($item) {
        return '<div class="card">'
             . '<h3>' . rex_escape($item['title']) . '</h3>'
             . '<p>' . rex_escape($item['lead']) . '</p>'
             . '</div>';
    });
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

## Sicherheit

`MFormOutput` führt **keinen** Escape auf Item-Werten durch — nur auf Tag-Attributen, die du selbst übergibst (`listAttrs`, Klassen). Im Template-Callback bist **du** für `rex_escape()` zuständig. Das gilt auch für `renderFragment()`.
