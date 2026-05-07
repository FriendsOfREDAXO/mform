# Media-Listen-Widgets

Dieses Kapitel beschreibt drei verwandte Widgets:

- `imagelist`: bildorientierte Liste auf Basis des Medialist-Speicherformats
- `custom_medialist`: flexibles Listen-Widget fuer Medien
- `custom_linklist`: flexibles Listen-Widget fuer interne Artikel-Links

## Verfuegbarkeit

| Widget | Klassisches Modul | `rex_form` | YForm | `REX_VAR` |
|---|---|---|---|---|
| `imagelist` | ja | ja | ja | ja |
| `custom_medialist` | ja | ja | ja | ja |
| `custom_linklist` | ja | ja | ja | ja |

## Klassisches Modul

### Mit MForm

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addFieldsetArea('Medien und Links', MForm::factory()
        ->addImagelistField(1, ['label' => 'Bildliste'])
        ->addMedialistField(2, ['label' => 'Dateiliste', 'views' => 'gallery,grid,list'])
        ->addLinklistField(3, ['label' => 'Linkliste'])
    )
    ->show();
```

### Direkt als PHP-Widget

```php
<?php
echo rex_var_imglist::getWidget('1', 'REX_INPUT_MEDIALIST[1]', 'REX_MEDIALIST[id=1]');

echo rex_var_custom_medialist::getWidget(
    '2',
    'REX_INPUT_MEDIALIST[2]',
    'REX_MEDIALIST[id=2]',
    [
        'views' => 'gallery,grid,list',
        'view' => 'gallery',
        'toolbar' => 'vertical',
    ]
);

echo rex_var_custom_linklist::getWidget(
    '3',
    'REX_INPUT_LINKLIST[3]',
    'REX_LINKLIST[id=3]',
    [
        'toolbar' => 'horizontal',
    ]
);
```

### Modul-Ausgabe

Alle drei Widgets bleiben zu den nativen Speicherformaten kompatibel.

```php
<?php
$images = array_filter(explode(',', 'REX_MEDIALIST[id=1]'));
$mediaFiles = array_filter(explode(',', 'REX_MEDIALIST[id=2]'));
$linkIds = array_filter(explode(',', 'REX_LINKLIST[id=3]'));

foreach ($images as $file) {
    echo '<img src="' . rex_url::media($file) . '" alt="' . rex_escape($file) . '">';
}

foreach ($mediaFiles as $file) {
    echo '<a href="' . rex_url::media($file) . '">' . rex_escape($file) . '</a>';
}

foreach ($linkIds as $linkId) {
    $article = rex_article::get((int) $linkId);
    if ($article) {
        echo '<a href="' . rex_getUrl($article->getId()) . '">' . rex_escape($article->getName()) . '</a>';
    }
}
```

## rex_form

### `imagelist`

```php
<?php
$field = $form->addField('', 'image_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_imglist_element'], true);
$field->setCategoryId(0);
$field->setTypes('jpg,png,webp,avif');
```

### `custom_medialist`

```php
<?php
$field = $form->addField('', 'media_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_medialist_element'], true);
$field->setCategoryId(0);
$field->setTypes('jpg,png,pdf');
$field->setView('gallery');
$field->setViews('gallery,grid,list');
$field->setToolbar('vertical');
```

### `custom_linklist`

```php
<?php
$field = $form->addField('', 'link_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_linklist_element'], true);
$field->setCategoryId(0);
$field->setToolbar('horizontal');
```

## YForm

### Verfuegbar

- `imagelist`
- `medialist`
- `linklist`

Fuer YForm stellt das Addon jetzt drei passende Value-Types bereit:

```php
<?php
$yform->setValueField('imagelist', [
    'name' => 'images',
    'label' => 'Bildliste',
    'category' => 0,
    'types' => 'jpg,png,webp,avif',
]);

$yform->setValueField('medialist', [
    'name' => 'media_files',
    'label' => 'Dateiliste',
    'category' => 0,
    'types' => 'jpg,png,pdf',
    'view' => 'gallery',
    'views' => 'gallery,grid,list',
    'toolbar' => 'vertical',
]);

$yform->setValueField('linklist', [
    'name' => 'links',
    'label' => 'Linkliste',
    'category' => 0,
    'toolbar' => 'horizontal',
]);
```

## REX_VAR

### `imagelist`

```html
REX_IMGLIST[id=1 widget=1 category=0 types="jpg,png,webp,avif"]
```

### `custom_medialist`

```html
REX_CUSTOM_MEDIALIST[id=2 widget=1 category=0 types="jpg,png,pdf" views="gallery,grid,list" view="gallery"]
```

### `custom_linklist`

```html
REX_CUSTOM_LINKLIST[id=3 widget=1 category=0 toolbar="horizontal"]
```

## Hinweise

- `imagelist` und `custom_medialist` speichern kommagetrennte Dateinamen.
- `custom_linklist` speichert kommagetrennte Artikel-IDs.
- In YForm heißen die passenden Value-Types `medialist` und `linklist`.
