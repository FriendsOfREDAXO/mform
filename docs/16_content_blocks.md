# Content Blocks (MForm 10)

Mit `addContentBlocksElement()` steht ein blockbasierter Editor zur Verfuegung, der auf dem Flex-Repeater basiert.

Startumfang:

- Ueberschrift
- Text (TinyMCE)
- Text und Bild

## Input: Block-Editor anlegen

```php
<?php

use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addContentBlocksElement(1, [
        'label' => 'Inhalte',
        'tiny_profile' => 'default',
        'btn_text' => 'Block hinzufuegen',
        'copy_paste' => true,
        'collapsed' => false,
    ])
    ->show();
```

Optionen:

- `tiny_profile` setzt das TinyMCE-Profil fuer Text-Felder.
- Repeater-Optionen wie `min`, `max`, `copy_paste`, `collapsed`, `btn_text` koennen direkt mitgegeben werden.

## Output: Framework-spezifisch rendern

Fuer die Ausgabe gibt es `MFormContentBlocksOutput` mit drei Template-Varianten:

- Bootstrap 5
- UIkit 3
- Bulma

```php
<?php

use FriendsOfRedaxo\MForm\Output\MFormContentBlocksOutput;

echo MFormContentBlocksOutput::from(1)->renderBootstrap5();
```

Alternativen:

```php
<?php

echo MFormContentBlocksOutput::from(1)->renderUIKit3();
echo MFormContentBlocksOutput::from(1)->renderBulma();
```

## Felder pro Block

### headline

- `headline`
- `headline_tag` (`h1` bis `h6`)

### text

- `title`
- `text` (TinyMCE-Textarea)

### text_image

- `title`
- `image` (MForm-Media-Widget)
- `image_alt`
- `image_position` (`left` oder `right`)
- `text` (TinyMCE-Textarea)

## Hinweis zur Erweiterung

Der Block-Builder ist bewusst schlank gehalten. Weitere Blocktypen koennen spaeter ueber eine erweiterte API oder ueber Projekt-Templates ergaenzt werden, ohne bestehende Inhalte zu brechen.
