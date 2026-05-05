# Erweiterte Beispiele

Kniffe und Praxisbeispiele, wie MForm-Elemente an die jeweiligen Anforderungen angepasst werden können.

MForm stellt folgende Element-Methoden bereit:

- Strukturelle Wrapper-Elemente
  - `addFieldsetArea`
    - `addConditionalFieldsetArea`
  - `addCollapseElement`
  - `addAccordionElement`
  - `addTabElement`
  - `addColumnElement`
  - `addInlineElement`
- Text-Input- und Hidden-Elemente
  - `addTextField`
  - `addHiddenField`
  - `addTextAreaField`
  - `addTextReadOnlyField`
  - `addTextAreaReadOnlyField`
- Select-Elemente
  - `addSelectField`
  - `addMultiSelectField`
- Checkbox- und Radio-Elemente
  - `addCheckboxField`
  - `addToggleCheckboxField`
  - `addRadioField`
  - `addRadioImgField`
  - `addRadioIconField`
  - `addRadioColorField`
- Informelle-Elemente
  - `addHtml`
  - `addHeadline`
  - `addDescription`
  - `addAlert`
  - `addAlertDanger`, `addAlertError`
  - `addAlertInfo`
  - `addAlertSuccess`
  - `addAlertWarning`
- System-Button-Elemente
  - `addLinkField`
  - `addLinklistField`
  - `addMediaField`
  - `addMedialistField`
- Custom-Elemente
  - `addCustomLinkField`
  - `addCustomLinkMultipleField`
  - `addImagelistField`
  - `addInputField`
- Repeater
  - `addRepeaterElement`
  - `addFlexRepeaterElement`
- Spezielle `setter`-Methoden
  - `setAttribute`
  - `setAttributes`
  - `setCategory`
  - `setCollapseInfo`
  - `setDefaultValue`
  - `setDisableOption`
  - `setDisableOptions`
  - `setFormItemColClass`
  - `setFull`
  - `setLabel`
  - `setLabelColClass`
  - `setMultiple`
  - `setOption`
  - `setOptions`
  - `setParameter`
  - `setParameters`
  - `setPlaceholder`
  - `setShowWrapper`
  - `setSize`
  - `setSqlOptions`
  - `setTabIcon`
  - `setToggleOptions`
  - `setTooltipInfo`

## Beispiele: Attribute

```php
// init mform
$mform = MForm::factory()
    // text input use set attribute method
    ->addTextField("1.0")
    ->setAttribute('label', 'Text Label')
    ->setAttribute('class', 'mynewclass')
    ->setAttribute('style', 'width: 260px')
    ->setAttribute('default-value', 'default value string');
// text input use set attributes method
$mform->addTextField(1.2)
    ->setAttributes([
        'label'=>'Text Label',
        'class'=>'mynewclass',
        'style'=>'width: 220px',
        'default-value'=>'default value string'
    ]);
// text input use add method attributes parameter
$mform->addTextField(1.3, [
    'label'=>'Text Label',
    'class'=>'mynewclass',
    'style'=>'width: 280px',
    'default-value'=>'default value string'
]);
// text input use any set methods
$mform->addTextField(1.4)
    ->setLabel('Text Label') // for label use set label method
    ->setAttribute('class', 'mynewclass') // for class use only set attribute method
    ->setAttribute('style', 'width: 220px') // for style use only set attribute method
    ->setDefaultValue('default value string'); // for default value use set default value method
// init mform
$mform2 = MForm::factory()
    // select use add method attributes parameter
    ->addSelectField("2.0", [1 => 'option 1', 2 => 'option 2'], [
        'label'=>'Select Label',
        'class'=>'mynewclass',
        'style'=>'width: 260px',
        'default-value'=>2
    ]);
// select use set attributes method
$mform->addSelectField(2.1, [1 => 'option 1', 2 => 'option 2'])
    ->setAttributes([
        'label'=>'Select Label',
        'class'=>'mynewclass',
        'style'=>'width: 220px',
        'default-value'=>2
    ]);
// select use any setters
$mform->addSelectField(2.2)
    ->setOptions([1 => 'option 1', 2 => 'option 2', 3 => 'option 3', 4 => 'option 4']) // for options set options method
    ->setOption('option 5', 5)
    ->setLabel('Select Label') // for label use set label method
    ->setAttribute('class', 'mynewclass') // for class use only set attribute method
    ->setAttribute('style', 'width: 260px') // for style use only set attribute method
    ->setDefaultValue(2) // for default value use set default value method
    ->setMultiple()
    ->setSize('full');
// parse mform
echo MForm::factory()
    // add fieldset areas
    ->addFieldsetArea('Fieldset Element', $mform)
    ->addFieldsetArea('Select elements with attributes', $mform2)
    ->show();
```

## Beispiele: JSON-Value-Support

```php
// instanziieren
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Full json value support', MForm::factory()
        ->addTextField('1.0.title', ['label' => 'Text title'])
        ->addTextAreaField("1.0.description", ['label' => 'Text description'])
        ->addMultiSelectField("1.0.style", [1 => 'test-1', 2 => 'test-2'], ['label' => 'Multiselect style'])
    )
    // add second fieldset area
    ->addFieldsetArea('Numeric json values', MForm::factory()
        ->addTextField("1.1.0", ['label' => 'Text input'])
        ->addTextAreaField("1.1.1", ['label' => 'Textarea'])
        ->addMultiSelectField("1.1.2", [1 => 'test-1', 2 => 'test-2'], ['label' => 'Multiselect'])
    );
// parse form
echo $mform->show();
```

```php
dump('REX_VALUE[id=1]');
```

## Beispiele: Optionen

```php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Select elements with options', MForm::factory()
        // select use add method options parameter
        ->addSelectField("1.0", [1 => 'option 1', 2 => 'option 2'], ['label'=>'Select Label'])
        // select use set option method
        ->addSelectField(1.1)
            ->setOption(1, 'option 1')
            ->setOption(2, 'option 2')
            ->setLabel('Select Label')
        // select use set options method
        ->addSelectField(1.2)
            ->setOptions([1 => 'option 1', 2 => 'option 2', 3 => 'option 3', 4 => 'option 4']) // for options set options method
            ->setLabel('Select Label') // for label use set label method
    )
    // add second fieldset area
    ->addFieldsetArea('Multiselect elements with options', MForm::factory()
        // multiselect use add method options parameter
        ->addMultiSelectField("2.0", [1 => 'option 1', 2 => 'option 2'], ['label'=>'Multiselect Label'])
        ->addSelectField("3.0", ['optgroup 1' => [1 => 'option 1', 2 => 'option 2'], 'optgroup 2' => [3 => 'option 3', 4 => 'option 4']], ['label'=>'Multiselect Label'])
            ->setSize('full') // do it full
            ->setMultiple()
        // select use set options method
        ->addSelectField(3.1)
            ->setOptions(['optgroup 1' => [1 => 'option 1', 2 => 'option 2'], 'optgroup 2' => [3 => 'option 3', 4 => 'option 4']]) // for options set options method
            ->setLabel('Multiselect Label')
            ->setMultiple()
    )
    // add third fieldset area
    ->addFieldsetArea('Checkbox element with option', MForm::factory()
        // checkbox element with option
        ->addCheckboxField(4)
            ->setOption(1, 'option 1') // checkboxes can only have one option
            ->setLabel('Checkbox Label')
    )
    // add fourth fieldset area
    ->addFieldsetArea('Radio button element with options', MForm::factory()
        ->addRadioField(5, [1 => 'option 1', 2 => 'option 2'], ['label' => 'Radio Buttons'])
            ->setOptions([1 => 'test-1', 2 => 'test-2', 3 => 'test-3']) // overwrite options
            ->setLabel('Radio Label') // overwrite label
    )
    // add fifth fieldset area
    ->addFieldsetArea('SQL options', MForm::factory()
        ->addRadioField(6, [], ['label' => 'SQL Radio'])
            ->setSqlOptions("select id, name from rex_module limit 5")
        ->addSelectField(7, [], ['label' => 'SQL Select'])
            ->setSqlOptions("select id, name from rex_module")
    );
// parse form
echo $mform->show();
```

## Beispiele: Mehrsprachigkeit

MForm unterstützt Mehrsprachigkeit der Formularbeschriftung auf verschidenen Ebenen.

```php
<?php
$repeater = MForm::factory();

$repeater->addTextField('test1', ['label' => ['en'=>'Label EN', 'de' => 'Label DE']]);
$repeater->addTextField('test2', ['label' => rex_i18n::msg("test")]);

echo $repeater->show();
?>
```

In der letzteren Variante muss als Label ein Schlüssel für die Übersetzung übergeben werden. Dieser Schlüssel inkl. Übersetzung wird dann in der passenden `lang`-Datei hinterlegt, bspw. im `project`-Addon.

## Beispiel: Conditional Fields Builder

Mit `addConditionalFieldsetArea` lassen sich Bereiche anhand eines Quellfeldes ein- und ausblenden.

```php
<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addFieldsetArea('Basis', MForm::factory()
        ->addSelectField(1, [
            'text' => 'Text',
            'image' => 'Bild',
            'video' => 'Video',
        ], ['label' => 'Content-Typ'])
        ->addToggleCheckboxField(2, [1 => 'Erweiterte Optionen aktivieren'], ['label' => 'Erweitert'])
    )
    ->addConditionalFieldsetArea(1, '=', 'text', 'Text-Optionen', MForm::factory()
        ->addTextField(3, ['label' => 'Text-Headline'])
        ->addTextAreaField(4, ['label' => 'Text-Inhalt'])
    )
    ->addConditionalFieldsetArea(1, '=', 'image', 'Bild-Optionen', MForm::factory()
        ->addMediaField(5, null, null, ['label' => 'Hauptbild'])
        ->addTextField(6, ['label' => 'Alt-Text'])
    )
    ->addConditionalFieldsetArea(2, '=', '1', 'Erweiterte Optionen', MForm::factory()
        ->addTextField(7, ['label' => 'CSS-Klasse'])
        ->addTextField(8, ['label' => 'Anker-ID'])
    );

echo $mform->show();
```

Unterstützte Operatoren:

- `=` / `==`
- `!=`
- `>`
- `<`
- `contains`
- `in` (kommagetrennte Vergleichswerte)
- `empty`
- `!empty`

Optionale Aktion:

- Standard ist `show` (Bereich zeigen, wenn Bedingung erfüllt ist)
- Mit dem letzten Parameter `action = 'hide'` wird das Verhalten invertiert

## Beispiel: LayoutPreviewBuilder mit addRadioImgField

`LayoutPreviewBuilder` ist in mform vor allem fuer grafische Auswahlfelder gedacht. Der typische Einsatz ist `addRadioImgField()`, bei dem jede Option eine automatisch erzeugte Layoutvorschau bekommt.

Direkte Nutzung, wenn eine Vorschau manuell erzeugt und als Bild ausgegeben werden soll:

```php
<?php

use FriendsOfRedaxo\MForm\Utils\LayoutPreviewBuilder;

$preview = (new LayoutPreviewBuilder())
    ->setAspectRatio('16:9')
    ->setBackgroundColor('#ffffff')
    ->addColumn('1/2')
    ->addElement('image', 'left', '4:3', ['description' => 'Bild'])
    ->addColumn('1/2')
    ->addElement('text', 'left', '4:3', ['description' => 'Text'])
    ->addElement('button', 'left', '3:1', ['description' => 'CTA'])
    ->render();

echo '<img src="' . $preview . '" alt="Layoutvorschau">';
```

Im Addon ist aber meist dieser Weg gemeint: `addRadioImgField()` erwartet pro Option eine `config`, die intern ueber `MFormLayoutPreviewHelper` und `LayoutPreviewBuilder` in ein Vorschaubild umgesetzt wird.

```php
<?php

use FriendsOfRedaxo\MForm;

$options = [
    'layout_a' => [
        'label' => 'Bild links, Text rechts',
        'config' => [
            'aspectRatio' => '16:9',
            'backgroundColor' => '#ffffff',
            'columns' => [
                [
                    'width' => '1/2',
                    'elements' => [
                        ['type' => 'image', 'aspectRatio' => '4:3', 'description' => 'Bild'],
                    ],
                ],
                [
                    'width' => '1/2',
                    'elements' => [
                        ['type' => 'text', 'aspectRatio' => '4:3', 'description' => 'Text'],
                        ['type' => 'button', 'aspectRatio' => '3:1', 'description' => 'Button'],
                    ],
                ],
            ],
        ],
    ],
    'layout_b' => [
        'label' => 'Text ueber Bild',
        'config' => [
            'aspectRatio' => '4:5',
            'backgroundColor' => '#ffffff',
            'columns' => [
                [
                    'width' => 'full',
                    'elements' => [
                        ['type' => 'text', 'aspectRatio' => '3:1', 'description' => 'Headline'],
                        ['type' => 'image', 'aspectRatio' => '4:3', 'description' => 'Visual'],
                    ],
                ],
            ],
        ],
    ],
];

echo MForm::factory()
    ->addRadioImgField(1, $options, ['label' => 'Layout-Auswahl'])
    ->show();
```

## Beispiel: HtmlToSvgConverter mit addRadioImgField

`HtmlToSvgConverter` ist ebenfalls vor allem fuer `addRadioImgField()` interessant. Statt einer Layout-Konfiguration kann eine Option ein `svgIconSet` enthalten. mform erzeugt daraus intern ein `img`-Tag fuer die Radio-Option.

Direkte Nutzung, wenn SVG-Markup manuell in ein Bild umgewandelt werden soll:

```php
<?php

use FriendsOfRedaxo\MForm\Utils\HtmlToSvgConverter;

$markup = '
    <rect x="40" y="40" width="520" height="300" style="fill:#f8f9fa;stroke:#ced4da;stroke-width:4px"></rect>
    <text x="300" y="190" style="font-size:42px;font-weight:700;text-align:center;fill:#495057">Demo</text>
';

$converter = new HtmlToSvgConverter();

echo $converter->convertToImgTag($markup, [
    'viewBox' => '0 0 600 380',
    'width' => '180px',
    'height' => '114px',
    'alt' => 'SVG-Vorschau',
]);
```

Der uebliche mform-Anwendungsfall ist aber ein Radio-Image-Feld mit `svgIconSet` pro Option:

```php
<?php

use FriendsOfRedaxo\MForm;

$options = [
    'teaser' => [
        'label' => 'Teaser',
        'svgIconSet' => '
            <rect x="20" y="20" width="240" height="160" style="fill:#0d6efd"></rect>
            <text x="140" y="105" style="font-size:28px;font-weight:700;text-align:center;fill:#ffffff">T</text>
        ',
    ],
    'accordion' => [
        'label' => 'Accordion',
        'svgIconSet' => '
            <rect x="20" y="20" width="240" height="36" style="fill:#e9ecef"></rect>
            <rect x="20" y="72" width="240" height="36" style="fill:#e9ecef"></rect>
            <rect x="20" y="124" width="240" height="36" style="fill:#e9ecef"></rect>
        ',
    ],
];

echo MForm::factory()
    ->addRadioImgField(2, $options, ['label' => 'Darstellung'])
    ->show();
```

## Beispiel: Templates / Defaults per Key

Fuer wiederkehrende Form-Defaults (z. B. Standard-Einstellungen, Basisfelder) kann ein Template-Key verwendet werden.
Die Template-Definition liegt dabei z. B. im `project`-Addon, die Verwendung bleibt in Modulen sehr schlank.

### 1) Template im project-Addon registrieren

Beispiel in `redaxo/src/addons/project/boot.php`:

```php
<?php

use FriendsOfRedaxo\MForm;
use FriendsOfRedaxo\Project\MFormTemplate\CardDefaultsTemplate;

MForm::registerTemplate('card_defaults', CardDefaultsTemplate::class);
```

### 2) Template im Modul verwenden

Neue Form direkt aus einem Key:

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::fromTemplate('card_defaults')
    ->addTextField('title', ['label' => 'Titel'])
    ->show();
```

Oder auf eine bestehende Form anwenden:

```php
<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTextField('headline', ['label' => 'Headline'])
    ->applyTemplate('card_defaults', ['module' => 'team_cards'])
    ->addTextAreaField('text', ['label' => 'Text']);

echo $mform->show();
```

Hinweise:

- Der Key ist frei waehlbar (`card_defaults`, `hero_defaults`, `teaser_defaults`, ...).
- Ueber `$context` koennen projektseitig Varianten gesteuert werden.
- Ohne passende Registrierung per `MForm::registerTemplate(...)` bleibt die Form unveraendert.
