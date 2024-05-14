
# REDAXO System-Elemente

Rendert die REDAXO System-Elemente `REX_MEDIA_BUTTON`, `REX_LINK_BUTTON`, `REX_MEDIALIST_BUTTON`, `REX_LINKLIST_BUTTON`.

## Modul-Eingabe

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Media file elements', MForm::factory()
        // some media fields
        ->addMediaField(1, array('label'=>'Image'))
        ->addMedialistField(1, array('label'=>'Image list'))
        ->addImagelistField(2, ['label' => 'Image List'])
    )
    // add second fieldset area
    ->addFieldsetArea('Link elements', MForm::factory()
        // some link elements
        ->addLinkField(1,array('label'=>'Link'))
        ->addLinklistField(1,array('label'=>'Link list'))
        ->addCustomLinkField(1, ['label' => 'Custom Link', 'data-intern' => 'enable', 'data-extern' => 'enable', 'data-media' => 'enable', 'data-mailto' => 'enable', 'data-tel' => 'enable'])
    );
// parse form
echo $mform->show();
```

## Modul-Ausgabe

```php
<?php
dump('REX_MEDIA[id=1]');
dump('REX_MEDIALIST[id=1]');
dump('REX_MEDIALIST[id=2]');
dump('REX_LINK[id=1]');
dump('REX_LINKLIST[id=1]');
dump('REX_VALUE[id=1]');
```

## Parameter

Die System-Elemente können mit Parametern versehen werden, z.B. um die Vorschau zu aktivieren oder die Kategorie eines Medienpool-Auswahlfelds festzulegen.

```php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('System Elements with Parameters', MForm::factory()
        // use media field method with parameter property
        ->addMediaField(1, ['types'=>'png', 'preview'=>1, 'category'=>2, 'label'=>'Image'])
        // use set parameter method
        ->addMediaField(2)
            ->setLabel('Image')
            ->setParameter('preview', 1)
            ->setParameter('category', 2)
            ->setParameter('type', 'png')
        // use set parameters method
        ->addMediaField(3)
            ->setLabel('Image')
            ->setParameters(['types'=>'png', 'preview'=>1, 'category'=>2])
        // use media list method with parameter property
        ->addMedialistField(1, ['types'=>'gif,jpg', 'preview'=>1, 'category'=>4, 'label'=>'Image list'])
        // use set parameters for link field
        ->addLinkField(1)
            ->setParameters(['label'=>'Link', 'category'=>3])
        // and for linklist field
        ->addLinklistField(1, ['label'=>'Link list'])
            ->setParameter('category', 2)
    );
// parse form
echo $mform->show();
```
