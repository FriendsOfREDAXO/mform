# MForm – REDAXO Addon

![Poster](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform8.png?raw=true)

MForm macht den Aufbau von REDAXO-Modul-Eingaben angenehm. Statt rohem HTML-Formular-Markup wird alles in sauberem PHP definiert — vom einfachen Textfeld bis hin zum vollständig verschachtelten, copy-paste-fähigen Repeater mit bedingter Anzeige von Feldbereichen.

Nicht nur für Module: MForm erweitert auch **YForm** und **rex_form** um zusätzliche Widgets wie das Custom-Link-Feld, Bildlisten oder den ColorSwatch-Picker.

## Was MForm kann

- Modul-Eingabeformulare per fluentem PHP-API (`MForm::factory()->addTextField(...)->...->show()`)
- **Flex-Repeater** für dynamisch wiederholbare Formularzeilen — mit Verschachtelung, Copy/Paste und Aktiv/Inaktiv pro Item
- Wiederverwendbare Formularlogik via **Template-API** (`registerTemplate`, `fromTemplate`, `applyTemplate`)
- Hilfsklassen für die Repeater-Ausgabe: `MFormRepeaterHelper::decode()`, `filterByField()`, `sortByField()`, `groupByField()`, `limitItems()`
- YForm Value-Types: `custom_link`, `custom_link_multi`, `color_swatch`
- Wachsende Bibliothek **installierbarer Demo-Module** direkt aus dem REDAXO-Backend

## Schnellbeispiel

```php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addTabElement('Inhalt', MForm::factory()
        ->addTextField(1, ['label' => 'Headline'])
        ->addColorSwatchField(2, [
            '#ffffff' => 'Weiß',
            '#111111' => 'Schwarz',
            '.bg-primary' => ['label' => 'Primär', 'preview' => '#0d6efd'],
        ], ['label' => 'Hintergrund'])
    )
    ->addTabElement('Items', MForm::factory()
        ->addRepeaterElement(3, MForm::factory()
            ->addTextField('title', ['label' => 'Titel'])
            ->addMediaField('image', ['label' => 'Bild'])
        , true, true, ['min' => 1, 'max' => 10, 'copy_paste' => true])
    )
    ->show();
```

## Dokumentation & Tutorial

Die vollständige Dokumentation ist direkt im REDAXO-Backend unter **MForm → Docs** verfügbar.

Für eine Schritt-für-Schritt-Einführung gibt es den **[FOR-Tutorial-Bereich](https://friendsofredaxo.github.io/tricks/)** mit MForm-spezifischen Beiträgen.

Wer von MBlock migriert, findet den Einstieg in [docs/08_mblock_migration.md](docs/08_mblock_migration.md).

## Installation

Direkt über den REDAXO-Installer installierbar — nach `mform` suchen, herunterladen und aktivieren.

## Lizenz

[MIT Lizenz](LICENSE.md)

## Credits

Danke an alle [Contributors](https://github.com/FriendsOfREDAXO/mform/graphs/contributors), die MForm über die Jahre mitgestaltet haben!

**Top Contributors**

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

Ein herzliches Dankeschön auch an [interweave-media](https://github.com/interweave-media), [olien](https://github.com/olien), [elricco](https://github.com/elricco), [staabm](https://github.com/staabm), [ascky-thorben](https://github.com/ascky-thorben), [dpf-dd](https://github.com/dpf-dd), [nandes2062](https://github.com/nandes2062), [dgrothaus-mc](https://github.com/dgrothaus-mc), [lexplatt](https://github.com/lexplatt), [danspringer](https://github.com/danspringer), [Geri2017](https://github.com/Geri2017), [omphteliba](https://github.com/omphteliba), [bitshiftersgmbh](https://github.com/bitshiftersgmbh), [TobiasKrais](https://github.com/TobiasKrais), [VIEWSION](https://github.com/VIEWSION), [christophboecker](https://github.com/christophboecker), [cukabeka](https://github.com/cukabeka) und [philippparth](https://github.com/philippparth).

**Ein Projekt von [Friends Of REDAXO](https://github.com/FriendsOfREDAXO)**
