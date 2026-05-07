# MForm außerhalb von Modulen

Dieses Kapitel beschreibt den Einsatz von MForm-Komponenten außerhalb der klassischen Modul-Eingabe.

Gemeint sind vor allem diese Kontexte:

- eigene Backend-Seiten im Addon
- `rex_form`
- YForm
- direkte Widget-Nutzung in eigenen Formularen

## Grundsatz

MForm ist nicht auf Modul-Eingaben beschraenkt.
Fuer den Einsatz außerhalb von Modulen gibt es drei bewaehrte Wege:

1. komplette Formulare weiterhin mit `MForm::factory()` bauen, wenn die Datenstruktur und das Rendering dazu passen
2. einzelne Widgets direkt per `::getWidget()` in eigene Backend-Formulare einbauen
3. die vorhandenen Integrationen fuer `rex_form` und YForm verwenden

Die Widget-Assets werden im Backend automatisch ueber `boot.php` eingebunden.

## Welche Integration ist wofuer gedacht?

| Kontext | Empfohlener Weg | Bemerkung |
|---|---|---|
| eigenes Addon-Formular | direkte `::getWidget()`-Aufrufe | maximale Kontrolle ueber Markup und Speichern |
| `rex_form` | `rex_form_widget_*_element` | sauber fuer CRUD-Backends und Tabellenpflege |
| YForm | Value-Types | ideal fuer Manager-/Datenset-Workflows |

## Eigene Backend-Formulare

Wenn du in einer eigenen Addon-Seite oder einem eigenen Controller ein Formular selbst rendert und speicherst, kannst du die Widgets direkt verwenden.

### Beispiel: Medialist in einer eigenen Backend-Seite

```php
<?php

$mediaValue = rex_post('settings', 'array', [])['media'] ?? '';

echo '<form action="' . rex_url::currentBackendPage() . '" method="post">';
echo rex_var_custom_medialist::getWidget(
    'settings_media',
    'settings[media]',
    (string) $mediaValue,
    [
        'view' => 'gallery',
        'views' => 'gallery,grid,list',
        'toolbar' => 'vertical',
    ]
);
echo '<p><button class="btn btn-save rex-form-aligned" type="submit">Speichern</button></p>';
echo '</form>';
```

### Beispiel: Linkliste in einer eigenen Backend-Seite

```php
<?php

$linkValue = rex_post('settings', 'array', [])['links'] ?? '';

echo rex_var_custom_linklist::getWidget(
    'settings_links',
    'settings[links]',
    (string) $linkValue,
    [
        'toolbar' => 'horizontal',
    ]
);
```

### Beispiel: Custom-Link-Multi in einer eigenen Backend-Seite

```php
<?php

$customLinks = rex_post('settings', 'array', [])['custom_links'] ?? '';

echo rex_var_custom_link_multi::getWidget(
    'settings_custom_links',
    'settings[custom_links]',
    (string) $customLinks,
    [
        'intern' => 1,
        'external' => 1,
        'media' => 1,
        'mailto' => 1,
        'btn_add' => 'Link hinzufuegen',
    ]
);
```

## rex_form

Fuer `rex_form` existieren eigene `form_element`-Klassen.

### Verfuegbar in `rex_form`

- `rex_form_widget_mform_imglist_element`
- `rex_form_widget_mform_medialist_element`
- `rex_form_widget_mform_linklist_element`
- `rex_form_widget_mform_customlink_element`
- `rex_form_widget_mform_custom_link_multi_element`

### Beispiel: `rex_form` mit MForm-Widgets

```php
<?php

$field = $form->addField('', 'media_list', null, ['internal::fieldClass' => 'rex_form_widget_mform_medialist_element'], true);
$field->setCategoryId(0);
$field->setTypes('jpg,png,pdf');
$field->setView('gallery');
$field->setViews('gallery,grid,list');

$links = $form->addField('', 'links', null, ['internal::fieldClass' => 'rex_form_widget_mform_linklist_element'], true);
$links->setCategoryId(0);

$custom = $form->addField('', 'custom_links', null, ['internal::fieldClass' => 'rex_form_widget_mform_custom_link_multi_element'], true);
$custom->setIntern(1);
$custom->setExternal(1);
$custom->setMedia(1);
$custom->setBtnAdd('Link hinzufuegen');
```

## YForm

Fuer die hier relevanten Media- und Link-Widgets ist YForm jetzt vollstaendig angebunden.

### Vorhandene YForm-Value-Types

- `imagelist`
- `medialist`
- `linklist`
- `custom_link`
- `custom_link_multi`

### Beispiel: YForm-Felder

```php
<?php

$yform->setValueField('imagelist', [
    'name' => 'images',
    'label' => 'Bildliste',
    'types' => 'jpg,png,webp,avif',
]);

$yform->setValueField('medialist', [
    'name' => 'media_files',
    'label' => 'Dateiliste',
    'types' => 'jpg,png,pdf',
    'view' => 'gallery',
    'views' => 'gallery,grid,list',
]);

$yform->setValueField('linklist', [
    'name' => 'links',
    'label' => 'Linkliste',
    'category' => 0,
]);

$yform->setValueField('custom_link', [
    'name' => 'link',
    'label' => 'Link',
    'intern' => 1,
    'external' => 1,
    'media' => 1,
    'mailto' => 1,
]);

$yform->setValueField('custom_link_multi', [
    'name' => 'links',
    'label' => 'Links',
    'intern' => 1,
    'external' => 1,
    'media' => 1,
    'btn_add' => 'Link hinzufuegen',
]);
```

## Empfehlung

- Fuer eigene Backend-Formulare: direkte Widget-Aufrufe verwenden.
- Fuer tabellenbasierte Backend-Masken: `rex_form` verwenden.
- Fuer datengetriebene Manager-Formulare: YForm verwenden.
- Wenn du eine manager-nahe Datenpflege brauchst, kannst du jetzt auch Medialist und Linklist direkt in YForm verwenden.