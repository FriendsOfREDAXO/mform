# CheckboxGroup-Widget

`addCheckboxGroupField()` rendert eine visuelle Mehrfachauswahl als Pill-/Tag-Buttons. Der gespeicherte Wert ist ein kommaseparierter String der aktiven Keys.

## Verfügbarkeit

| Kontext | Unterstützt |
|---|---|
| Klassisches Modul (MForm standalone) | ✅ ja |
| Flex-Repeater (`addRepeaterElement`) | ✅ ja |
| `rex_form` | ✅ ja |
| YForm | ✅ ja |
| **MBlock (klassisches HTML-Widget)** | ❌ **nein** |

> **MBlock-Hinweis:** `addCheckboxGroupField()` funktioniert **nicht** im klassischen MBlock-HTML-Modus (`MBlock::show($id, $htmlString)`). MBlock speichert und liest Feldwerte ausschließlich über native Input-Elemente mit korrektem `name`-Attribut. Das CheckboxGroup-Widget arbeitet mit einem Hidden-Input und JS-gesteuerten Labels – das ist mit MBlocks Reindex-Mechanismus nicht kompatibel. Wer in MBlock eine Mehrfachauswahl braucht, sollte `addMultiSelectField()` oder mehrere `addCheckboxField()` verwenden.

## Signatur

```php
addCheckboxGroupField(
    float|int|string $id,
    ?array $options = null,
    ?array $attributes = null
): MForm
```

| Parameter | Typ | Beschreibung |
|---|---|---|
| `$id` | `string\|int\|float` | Feld-ID / JSON-Pfad (z. B. `1`, `"$id.0.tags"`) |
| `$options` | `array\|null` | Assoziatives Array `['key' => 'Label', ...]` |
| `$attributes` | `array\|null` | Optionale Attribute (siehe unten) |

### Unterstützte Attribute

| Attribut | Typ | Default | Beschreibung |
|---|---|---|---|
| `label` | `string` | – | Feldbezeichnung |
| `layout` | `'horizontal'\|'vertical'` | `'horizontal'` | Anordnung der Optionen |
| `mode` | `'checkbox'\|'radio'` | `'checkbox'` | Auswahl-Modus (Mehrfach vs. Einfach) |
| `notice` | `string` | – | Hilfstext unterhalb des Feldes |
| `default-value` | `string` | – | Kommaseparierter Default-Wert, z. B. `'news,blog'` |

## Gespeicherter Wert

Der Wert wird als kommaseparierter String gespeichert:

```
news,blog,event
```

Auslesen im Output-Code:

```php
$tags = rex_var::toArray('REX_VALUE[id=1&output=json]')['tags'] ?? '';
$selected = array_filter(explode(',', $tags));

if (in_array('news', $selected)) {
    // News-Kategorie aktiv
}
```

## Beispiele

### Einfache horizontale Auswahl

```php
echo MForm::factory()
    ->addCheckboxGroupField(1, [
        'news'     => 'News',
        'blog'     => 'Blog',
        'event'    => 'Events',
        'tutorial' => 'Tutorial',
    ], ['label' => 'Kategorien'])
    ->show();
```

### Mit Default-Wert

```php
echo MForm::factory()
    ->addCheckboxGroupField(1, [
        'news'  => 'News',
        'blog'  => 'Blog',
        'event' => 'Events',
    ], ['label' => 'Kategorien', 'default-value' => 'news,blog'])
    ->show();
```

### Vertikales Layout

```php
echo MForm::factory()
    ->addCheckboxGroupField(1, [
        'high'   => 'Hoch',
        'medium' => 'Mittel',
        'low'    => 'Niedrig',
    ], ['label' => 'Priorität', 'layout' => 'vertical'])
    ->show();
```

### Radio-Mode (Einfachauswahl)

Durch `'mode' => 'radio'` wird aus der CheckboxGroup eine Einfachauswahl mit runden Indikatoren. Das Ergebnis ist ein einzelner String-Wert ohne Komma.

```php
echo MForm::factory()
    ->addCheckboxGroupField(1, [
        'low'    => 'Niedrig',
        'medium' => 'Mittel',
        'high'   => 'Hoch',
    ], ['label' => 'Priorität', 'mode' => 'radio'])
    ->show();
```

Radio-Mode und Layout lassen sich kombinieren:

```php
->addCheckboxGroupField(1, [
    'daily'   => 'Täglich',
    'weekly'  => 'Wöchentlich',
    'monthly' => 'Monatlich',
], ['label' => 'Intervall', 'mode' => 'radio', 'layout' => 'vertical'])
```

**Verhalten im Radio-Mode:**
- Klick auf eine inaktive Option aktiviert sie und deaktiviert alle anderen
- Klick auf die aktive Option deaktiviert sie (Abwahl möglich)
- Der Indikator ist rund (wie `<input type="radio">`) statt eckig
- Gespeicherter Wert: einzelner String, z. B. `medium` (kein Komma)

**Auslesen im Output:**

```php
$priority = REX_VALUE[id=1];
// oder aus einem Repeater-Item:
$priority = $item['priority'] ?? '';

if ($priority === 'high') {
    // Hohe Priorität
}
```

### Im Flex-Repeater

```php
$rowForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addCheckboxGroupField('tags', [
        'news'     => 'News',
        'blog'     => 'Blog',
        'event'    => 'Events',
        'tutorial' => 'Tutorial',
    ], ['label' => 'Tags'])
    ->addCheckboxGroupField('priority', [
        'low'    => 'Niedrig',
        'medium' => 'Mittel',
        'high'   => 'Hoch',
    ], ['label' => 'Priorität', 'mode' => 'radio']);

echo MForm::factory()
    ->addRepeaterElement(1, $rowForm, true, true, [
        'label'    => 'Einträge',
        'btn_text' => 'Eintrag hinzufügen',
    ])
    ->show();
```

### Auslesen im Repeater-Output

```php
$items = rex_var::toArray('REX_VALUE[id=1&output=json]') ?? [];

foreach ($items as $item) {
    $tags  = array_filter(explode(',', $item['tags']  ?? ''));
    $types = array_filter(explode(',', $item['types'] ?? ''));

    echo '<div>';
    echo '<h3>' . rex_escape($item['title']) . '</h3>';

    if ($tags) {
        echo '<ul>';
        foreach ($tags as $tag) {
            echo '<li>' . rex_escape($tag) . '</li>';
        }
        echo '</ul>';
    }
    echo '</div>';
}
```

## MBlock-Alternative

Wer in **MBlock** eine Mehrfachauswahl benötigt, verwendet stattdessen:

```php
// Option A: Multi-Select (speichert kommasepariert)
$mform = MForm::factory()
    ->addMultiSelectField(1, [
        'news'  => 'News',
        'blog'  => 'Blog',
        'event' => 'Events',
    ], ['label' => 'Kategorien']);

echo MBlock::show(1, $mform->show());
```

```php
// Option B: Einzelne Checkboxen
$mform = MForm::factory()
    ->addCheckboxField('1.0.cat_news',  [1 => 'News'],   ['label' => ''])
    ->addCheckboxField('1.0.cat_blog',  [1 => 'Blog'],   ['label' => ''])
    ->addCheckboxField('1.0.cat_event', [1 => 'Events'], ['label' => 'Kategorien']);

echo MBlock::show(1, $mform->show());
```
