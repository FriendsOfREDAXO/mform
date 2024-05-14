# ImageList-Widget

Die ImageList ist ein Widget, das es ermöglicht, eine Liste von Bildern zu erstellen.

## Modul-Eingabe

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
echo MForm::factory()
    // add fieldset area
    ->addFieldsetArea('MForm Widgets', MForm::factory()
        // custom elements
        ->addImagelistField(1, ['label' => 'Image List'])
    )
    // parse form
    ->show();
```

## Modul-Ausgabe

Verwende die Imagelist wie eine klassische Medialist.

```php
<?php
dump('REX_MEDIALIST[id=1]');
```
