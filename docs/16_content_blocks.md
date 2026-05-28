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

## Eigene Blocktypen registrieren

Eigene Bloecke koennen global registriert werden. Dazu wird pro Blocktyp eine Form-Factory hinterlegt.

```php
<?php

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MForm\Content\MFormContentBlocks;

MFormContentBlocks::registerBlock('quote', 'Zitat', static function (array $options): MForm {
    return MForm::factory()
        ->addTextAreaField('quote', ['label' => 'Zitat'])
        ->addTextField('author', ['label' => 'Autor']);
});
```

Danach erscheint der neue Typ automatisch in `addContentBlocksElement()`.

## Eigene Renderer registrieren

Fuer eigene Blocktypen kann pro Framework ein Renderer registriert werden:

```php
<?php

use FriendsOfRedaxo\MForm\Output\MFormContentBlocksOutput;

MFormContentBlocksOutput::registerRenderer('bootstrap5', 'quote', static function (array $item): string {
    $quote = isset($item['quote']) && is_string($item['quote']) ? $item['quote'] : '';
    $author = isset($item['author']) && is_string($item['author']) ? $item['author'] : '';

    if ('' === trim(strip_tags($quote))) {
        return '';
    }

    $authorHtml = '' !== trim($author) ? '<footer class="blockquote-footer">' . rex_escape($author) . '</footer>' : '';

    return '<blockquote class="blockquote mb-4"><p>' . MFormOutput::richtext($quote) . '</p>' . $authorHtml . '</blockquote>';
});
```

Fuer UIkit und Bulma koennen analog eigene Renderer mit `uikit3` bzw. `bulma` registriert werden.
