# Templates und Defaults per Key

Mit MForm-Templates koennen wiederkehrende Form-Bloecke zentral im Projekt definiert und in Modulen wiederverwendet werden.

Empfehlung bei mehreren Templates (z. B. 5+):

- verwende eine Registry als zentrale Zuordnung `key -> Klasse`
- halte `project/boot.php` sehr schlank
- definiere je Template eine eigene Klasse

Wichtig:

- mform liefert die Registry selbst mit
- im Projekt-Boot registrierst du nur `key + Klassenname`
- `MForm::fromTemplate()` und `->applyTemplate()` greifen intern auf diese Registry zu

## 1) Empfohlene Struktur im Projekt

Beispielstruktur im project-Addon:

- `lib/MFormTemplate/TemplateInterface.php`
- `lib/MFormTemplate/CardDefaultsTemplate.php`
- `lib/MFormTemplate/HeroDefaultsTemplate.php`
- `lib/MFormTemplate/SeoDefaultsTemplate.php`
- `lib/MFormTemplate/TemplateRegistry.php`

## 2) Interface fuer alle Templates

```php
<?php

namespace FriendsOfRedaxo\Project\MFormTemplate;

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\MFormTemplate\TemplateInterface as MFormTemplateInterface;

interface TemplateInterface extends MFormTemplateInterface
{
}
```

## 3) Ein konkretes Template als Klasse

```php
<?php

namespace FriendsOfRedaxo\Project\MFormTemplate;

use FriendsOfRedaxo\MForm;

final class CardDefaultsTemplate implements TemplateInterface
{
    public function apply(MForm $form, array $context = []): MForm
    {
        $styleOptions = [
            'default' => 'Standard',
            'muted' => 'Muted',
            'primary' => 'Primary',
        ];

        if (($context['variant'] ?? '') === 'dark') {
            $styleOptions['dark'] = 'Dark';
        }

        return $form
            ->addSelectField('card.style', $styleOptions, [
                'label' => 'Kartenstil',
                'default-value' => 'default',
            ])
            ->addToggleCheckboxField('card.linkdiv', [1 => 'Kachel komplett verlinken'], [
                'label' => 'Linkverhalten',
            ])
            ->addSelectField('card.shadow', [
                '' => 'Standard',
                'uk-card-hover' => 'Nur Hover',
                'uk-shadow-remove' => 'Kein Schatten',
            ], [
                'label' => 'Schatten',
            ]);
    }
}
```

## 4) Boot-Datei: nur Key + Klasse registrieren

Lege die Registrierung in `redaxo/src/addons/project/boot.php` ab.

```php
<?php

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\Project\MFormTemplate\CardDefaultsTemplate;
use FriendsOfRedaxo\Project\MFormTemplate\HeroDefaultsTemplate;
use FriendsOfRedaxo\Project\MFormTemplate\SeoDefaultsTemplate;

MForm::registerTemplate('card_defaults', CardDefaultsTemplate::class);
MForm::registerTemplate('hero_defaults', HeroDefaultsTemplate::class);
MForm::registerTemplate('seo_defaults', SeoDefaultsTemplate::class);
```

## 5) Verwendung in Modulen

```php
<?php

use FriendsOfRedaxo\MForm;

echo MForm::fromTemplate('card_defaults')
    ->addTextField('title', ['label' => 'Titel'])
    ->addTextAreaField('text', ['label' => 'Text'])
    ->show();
```

```php
<?php

use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTextField('headline', ['label' => 'Headline'])
    ->applyTemplate('card_defaults', ['module' => 'team_cards', 'variant' => 'dark'])
    ->addTextAreaField('body', ['label' => 'Beschreibung']);

echo $mform->show();
```

```php
<?php

use FriendsOfRedaxo\MForm;

$itemForm = MForm::fromTemplate('card_defaults')
    ->addTextField('title', ['label' => 'Titel'])
    ->addMediaField('image', ['label' => 'Bild']);

echo MForm::factory()
    ->addRepeaterElement(1, $itemForm, true, true, [
        'label' => 'Karten',
        'btn_text' => 'Karte hinzufuegen',
        'copy_paste' => true,
    ])
    ->show();
```

## Hinweise

- Benenne Keys konsistent, z. B. `card.base`, `card.dark`, `seo.base`.
- Halte den `$context` bewusst klein und dokumentiert.
- Ohne registrierten Key bleibt die Form unveraendert.
- Templates koennen kombiniert werden, z. B. `->applyTemplate('base')->applyTemplate('seo')`.

## Design-Tokens (ab 10.0)

Alle MForm-Widgets (Custom Link, Listen, Flex-Repeater, Checkbox-Gruppe, Farb- und Range-Felder, Tabs) nutzen eine gemeinsame Palette aus `assets/css/mform-tokens.css`. Die Widget-Stylesheets mappen ihre eigenen Variablen (`--mform-custom-link-*`, `--mfr-*`, `--mfl-*`) auf diese Basis. Wer das Erscheinungsbild anpassen will, überschreibt die Tokens im Projekt-CSS statt einzelne Widget-Regeln:

```css
/* Light */
:root {
    --mform-link: #8a2be2;
    --mform-active-bg: #f1e6fb;
    --mform-radius: 2px;
}
/* Dark */
body.rex-theme-dark {
    --mform-link: #c39bff;
}
```

| Token | Verwendung |
|---|---|
| `--mform-surface`, `--mform-surface-alt` | Flächen (Widget, Toolbar, Item-Kopf) |
| `--mform-text`, `--mform-muted` | Text, Hinweise, Icons |
| `--mform-border`, `--mform-border-strong`, `--mform-input-border` | Linien und Rahmen |
| `--mform-hover`, `--mform-active-bg`, `--mform-active-text` | Hover und aktive Zustände |
| `--mform-link`, `--mform-focus`, `--mform-focus-ring` | Akzent, Fokus |
| `--mform-input-bg` | Eingabefelder |
| `--mform-success`, `--mform-danger`, `--mform-warning` | Statusfarben (Hinzufügen, Löschen, Warnung) |
| `--mform-badge-bg`, `--mform-badge-text` | Badges (Anzahl, Typ) |
| `--mform-radius`, `--mform-radius-lg` | Eckenradius Felder/Buttons (Standard 0, eckig wie das Backend) bzw. Container (Repeater-Items, Listen-Rahmen) |
| `--mform-shadow`, `--mform-shadow-lift` | Schatten (Ruhe, Drag) |

Die Palette ist auf Linkmap und MediaPlace abgestimmt. Das Dark-Theme greift über `body.rex-theme-dark`, bei Theme „Auto“ über `prefers-color-scheme: dark` in Verbindung mit `body.rex-has-theme:not(.rex-theme-light)`.

## Wrapper-Fragment (ab 10.0)

Das Markup aller Wrapper (Fieldset, Collapse, Spalten, Tabs, Modal) liegt in `fragments/mform/mform_wrapper.php`. Seit 10.0 nutzt auch der Flex-Repeater dieses Fragment (über `FriendsOfRedaxo\MForm\Template\MFormWrapperRenderer`), vorher hatte er das Markup nachgebaut. Dasselbe gilt für die Feld-Zeile: `fragments/mform/mform_default.php` liefert form-group, Label-Spalte und Feld-Spalte in beiden Pfaden; im Repeater kommen die Klassen `row mfr-field-group`, `mfr-field-label` und `mfr-field-col` dazu. Wer das Fragment im Projekt überschreibt (`fragments/mform/mform_wrapper.php` im Project-Addon oder ein eigenes Theme), ändert damit beide Pfade. Der Paritäts-Test `tests/Redaxo/Parser/RenderParityTest.php` hält das Ergebnis beider Pfade als Snapshot fest.
