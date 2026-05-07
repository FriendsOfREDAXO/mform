# MForm – REDAXO Addon

![Poster](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform8.png?raw=true?2)

MForm takes the pain out of building REDAXO module inputs. Instead of writing raw HTML form markup, everything gets defined in clean PHP — from a simple text field to fully nested, copy-paste-capable repeaters with conditional logic.

Not just for modules: MForm also extends **YForm** and **rex_form** with custom widgets like the custom link field, image lists, or the ColorSwatch picker.

## What MForm does

- Builds module input forms via a fluent PHP API (`MForm::factory()->addTextField(...)->...->show()`)
- Provides a **Flex Repeater** for dynamically repeatable form rows — supporting nesting, copy/paste, and per-item enable/disable
- Ships reusable form logic via a **Template API** (`registerTemplate`, `fromTemplate`, `applyTemplate`)
- Adds helper classes for Repeater output: `MFormRepeaterHelper::decode()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()`
- Adds YForm value types: `custom_link`, `custom_link_multi`, `color_swatch`
- Offers a growing library of **installable demo modules** directly from the REDAXO backend

## Quick example

```php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addTabElement('Content', MForm::factory()
        ->addTextField(1, ['label' => 'Headline'])
        ->addColorSwatchField(2, [
            '#ffffff' => 'White',
            '#111111' => 'Black',
            '.bg-primary' => ['label' => 'Primary', 'preview' => '#0d6efd'],
        ], ['label' => 'Background'])
    )
    ->addTabElement('Items', MForm::factory()
        ->addRepeaterElement(3, MForm::factory()
            ->addTextField('title', ['label' => 'Title'])
            ->addMediaField('image', ['label' => 'Image'])
        , true, true, ['min' => 1, 'max' => 10, 'copy_paste' => true])
    )
    ->show();
```

## Documentation & Tutorial

The full documentation is available directly inside the REDAXO backend under **MForm → Docs**.

For a step-by-step introduction, check out the **[FOR Tutorial](https://friendsofredaxo.github.io/tricks/)** section on MForm.

MBlock users looking to migrate should start with [docs/08_mblock_migration.md](docs/08_mblock_migration.md).

## Installation

Install directly via the REDAXO Installer — search for `mform`, download and activate.

## License

[MIT License](LICENSE.md)

## Credits

Thanks to all [contributors](https://github.com/FriendsOfREDAXO/mform/graphs/contributors) who helped shape MForm over the years!

**Top contributors**

| | Contributor |
|---|---|
| 1 | [ynamite](https://github.com/ynamite) |
| 2 | [marcohanke](https://github.com/marcohanke) |
| 3 | [eaCe](https://github.com/eaCe) |
| 4 | [schuer](https://github.com/schuer) |
| 5 | [dtpop](https://github.com/dtpop) |
| 6 | [IngoWinter](https://github.com/IngoWinter) |
| 7 | [thorol](https://github.com/thorol) |
| 8 | [DanielWeitenauer](https://github.com/DanielWeitenauer) |

A big thank you also to [interweave-media](https://github.com/interweave-media), [olien](https://github.com/olien), [elricco](https://github.com/elricco), [staabm](https://github.com/staabm), [ascky-thorben](https://github.com/ascky-thorben), [dpf-dd](https://github.com/dpf-dd), [nandes2062](https://github.com/nandes2062), [dgrothaus-mc](https://github.com/dgrothaus-mc), [lexplatt](https://github.com/lexplatt), [danspringer](https://github.com/danspringer), [Geri2017](https://github.com/Geri2017), [omphteliba](https://github.com/omphteliba), [bitshiftersgmbh](https://github.com/bitshiftersgmbh), [TobiasKrais](https://github.com/TobiasKrais), [VIEWSION](https://github.com/VIEWSION), [christophboecker](https://github.com/christophboecker), [cukabeka](https://github.com/cukabeka) and [philippparth](https://github.com/philippparth).

**A [Friends Of REDAXO](https://github.com/FriendsOfREDAXO) project**
