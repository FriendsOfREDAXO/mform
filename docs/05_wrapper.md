# Wrapper- und Layout-Elemente

Diese Moduleingabe-Beispiele zeigen, wie man mittels der MForm Wrapper-Elemente Input-Formulare sinnvoll layouten und interaktiv aufbereitet.

## Verfügbarkeit

| Methode | Klassisches Modul | `rex_form` | YForm |
|---|---|---|---|
| `addFieldsetArea` | ja | ja | ja |
| `addCollapseElement` | ja | ja | – |
| `addAccordionElement` | ja | ja | – |
| `addTabElement` | ja | ja | – |
| `addColumnElement` | ja | ja | – |
| `addInlineElement` | ja | ja | – |
| `addModalElement` | ja | ja | – |

## Accordion

Stellt ein Accordion-Element dar, das sich bei Klick öffnet und schließt.

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // fieldset
    ->addFieldsetArea('Collapse accordion elements', MForm::factory()
        ->addAccordionElement('Accordion 1', MForm::factory()
            ->addTextAreaField('1.0.1', ['label' => 'Text-Area 1'])
        )
        ->addAccordionElement('Accordion 2', MForm::factory()
            ->addTextAreaField('1.0.2', ['label' => 'Text-Area 2'])
        , true) // open this collapse initial
        ->addAccordionElement('Accordion 3', MForm::factory()
            ->addTextAreaField('1.0.3', ['label' => 'Text-Area 3'])
        )
    );
// parse mform
echo $mform->show();
```

## Collapse

Stellt ein Collapse-Element dar, das sich bei Klick öffnet und schließt.

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // fieldset
    ->addFieldsetArea('Collapse elements', MForm::factory()
        ->addCollapseElement('Collapse 1', MForm::factory()
            ->addTextAreaField('5.0.1', ['label' => 'Text-Area 1'])
        , true) // open this collapse initial
        ->addCollapseElement('Collapse 2', MForm::factory()
            ->addTextAreaField('5.0.2', ['label' => 'Text-Area 2'])
        )
        ->addCollapseElement('Collapse 3', MForm::factory()
            ->addTextAreaField('5.0.3', ['label' => 'Text-Area 3'])
        )
    );
// parse mform
echo $mform->show();
```

## Radio-/Checkbox-Collapse

Stellt ein Collapse-Element dar, das sich in Abhängigkeit einer Checkbox- oder eines Radio-Auswahlfelds bei Klick öffnet und schließt.

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // toggle radio
    ->addFieldsetArea('Radio collapse element', MForm::factory()
        ->addRadioField('4.0.1', [1 => 'Open Collapse 1', 2 => 'Open Collapse 2' ,3 => 'Something...'], ['label' => 'Radio Buttons'])
            ->setToggleOptions([1 => 'collapse1', 2 => 'collapse2']) // user value "collapse1" and "collapse2" for collapse data-group-collapse-id
        ->addForm( // use addForm method to add the collapse element wrapper form
            MForm::factory()
                ->addCollapseElement('',
                    MForm::factory()->addTextAreaField('4.0.2', ['label' => 'Text-Area 1']),
                    false, true, ['data-group-collapse-id' => 'collapse1'] // open that collapse initial and hide the toggle link
                )
                ->addCollapseElement('',
                    MForm::factory()->addTextAreaField('4.0.3', ['label' => 'Text-Area 2']),
                    true, true, ['data-group-collapse-id' => 'collapse2'] // hide the toggle link
                )
        )
    )
    // toggle checkbox
    ->addFieldsetArea('Checkbox collapse element', MForm::factory()
        ->addCheckboxField('5.0.1', [1 => 'Open Collapse-area'], ['label' => 'Collapse checkbox', 'data-toggle-item' => 'collapse1'])
        ->addForm(MForm::factory()
            ->addCollapseElement('', MForm::factory()
                ->addTextField('5.0.2', ['label' => 'Button-Text (optional)'])
                ->addCustomLinkField('5.0.3', ['label' => 'Link-Ziel'])->show(), false, true, ['data-group-collapse-id' => 'collapse3']
            )
        )
    )
    // checkbox
    ->addFieldsetArea('Toggle checkbox collapse element', MForm::factory()
        ->addToggleCheckboxField('6.0.4', [1 => 'Open Collapse-area'], ['label' => 'Collapse checkbox', 'data-toggle-item' => 'collapse3'])
        ->addForm(MForm::factory()
            ->addCollapseElement('', MForm::factory()
                ->addTextField('6.0.5', ['label' => 'Button-Text (optional)'])
                ->addCustomLinkField('6.0.6', ['label' => 'Link-Ziel'])->show(), false, true, ['data-group-collapse-id' => 'collapse3']
            )
        )
    );
// parse form
echo $mform->show();
```

## Select-Collapse

Stellt ein Collapse-Element dar, das sich in Abhängigkeit einer Select-Auswahlliste bei Klick öffnet und schließt.

```php
<?php
// select collapse
$mform = MForm::factory()
    ->addFieldsetArea('Select collapse element',
        MForm::factory()
            ->addSelectField('6.0.select', [0 => 'Bitte wählen', 1 => 'collapse 1', 2 => 'collapse 2', 3 => 'collapse 3'], ['label' => 'Select collapse', 'data-toggle' => 'collapse'])
                ->setToggleOptions([1 => 'collapse1', 2 => 'collapse2', 3 => 'collapse3']) // user value "collapse1", "collapse2" and "collapse3" for collapse data-group-collapse-id
            ->addForm(
                MForm::factory()
                    ->addCollapseElement('',
                        MForm::factory()->addTextAreaField('6.0.1', ['label' => 'Text-Area 1']),
                        false, true, ['data-group-collapse-id' => 'collapse1']
                    )
                    ->addCollapseElement('',
                        MForm::factory()->addTextAreaField('6.0.2', ['label' => 'Text-Area 2']),
                        true, true, ['data-group-collapse-id' => 'collapse2']
                    )
                    ->addCollapseElement('',
                        MForm::factory()->addTextAreaField('6.0.3', ['label' => 'Text-Area 3']),
                        false, true, ['data-group-collapse-id' => 'collapse3']
                    )
            )
    );
// parse mform
echo $mform->show();
```

## Grid/Column-Wrapper

Stellt ein Grid-Element dar, das sich in mehrere Spalten aufteilen lässt.

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    ->addColumnElement(6, // column
        MForm::factory()->addTextField(1, ['label' => 'Test 1', 'full' => true])
    )
    ->addColumnElement(6, //column
        MForm::factory()->addTextField(2, ['label' => 'Test 2', 'full' => true]),
        ['class' => 'pl-0', 'data-test' => 'test123']
    );
// parse mform
echo $mform->show();
```

## Inline-Elemente

Stellt Inline-Elemente dar, die sich in einer Zeile nebeneinander anordnen.

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    ->addInlineElement('Label', MForm::factory()
        ->addTextField(3, ['label' => 'Test 3', 'full' => true])
        ->addTextField(4, ['label' => 'Test 4', 'full' => true])
    )
    ->addInlineElement('Label 2', MForm::factory()
        ->addTextField(5, ['label' => 'Test 5', 'full' => true])
        ->addTextField(6, ['label' => 'Test 6', 'full' => true])
    );
// parse mform
echo $mform->show();
```

## Tabs

Stellt Tab-Elemente dar, die bei Klick den dargestellten Inhalt wechseln.

Optional kann die Darstellung modernisiert oder vertikal (Navigation links) ausgegeben werden.

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    ->addTabElement('Tab1', MForm::factory()
        ->addTextField('2.0.1',['label' => 'Text1'])
            ->setOptions([1=>'option1',2=>'option2'])
    )
    ->addTabElement('Tab2', MForm::factory()
        ->addTextField('2.0.2',['label' => 'Text2'])
        , true, true
    )
    ->addTabElement('Tab3', MForm::factory()
        ->addTextField('2.0.3',['label' => 'Text3'])
    );
// parse mform
echo $mform->show();
```

### Optionale Tab-Varianten

```php
<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTabElement('Inhalt', MForm::factory()
        ->addTextField('1.0.1', ['label' => 'Titel'])
    , true, false, [
        'tab-icon' => 'fa-file-text-o',
        'tab-style' => 'modern',
        'tab-layout' => 'vertical',
    ])
    ->addTabElement('Einstellungen', MForm::factory()
        ->addCheckboxField('1.0.2', ['label' => 'Aktiv'])
    , false, false, [
        'tab-icon' => 'fa-cog',
    ]);

echo $mform->show();
```

Hinweise:

- `tab-style => 'modern'` ist optional und aktiviert eine modernisierte Optik.
- `tab-layout => 'vertical'` ist optional und zeigt die Tab-Navigation links neben dem Content.
- Alternativ koennen die Roh-Attribute `data-group-tab-style` und `data-group-tab-layout` gesetzt werden.

## Modal

Öffnet ein Bootstrap-Modal mit einem Sub-Formular. Ein Button wird direkt im Formular eingebettet; alle Felder im Modal-Inhalt werden beim Speichern des Moduls normal übernommen – kein separater AJAX-Request erforderlich.

**Parameter:**

| Parameter | Typ | Standard | Beschreibung |
|---|---|---|---|
| `$label` | `string` | `''` | Beschriftung des Trigger-Buttons und Modal-Titels |
| `$form` | `MForm, callable, string, null` | `null` | Sub-Formular als MForm-Instanz, Callable oder HTML-String |
| `$btnClass` | `string` | `'btn-default'` | CSS-Klassen für den Button (wird automatisch mit `btn` ergänzt) |
| `$align` | `string` | `'left'` | Button-Ausrichtung: `'left'`, `'center'` oder `'right'` |
| `$attributes` | `array` | `[]` | Zusätzliche HTML-Attribute für den Button-Wrapper |

```php
<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTextField(1, ['label' => 'Überschrift'])
    ->addTextAreaField(2, ['label' => 'Text'])

    // Modal für Block-Einstellungen, Button zentriert
    ->addModalElement('Block-Einstellungen', MForm::factory()
        ->addSelectField(10, [
            '' => '– Standard –',
            'bg-light' => 'Hellgrau',
            'bg-dark text-white' => 'Dunkel',
        ], ['label' => 'Hintergrundfarbe'])
        ->addSelectField(11, [
            'py-2' => 'Klein',
            'py-4' => 'Mittel',
        ], ['label' => 'Abstand'])
    , 'btn-default', 'center')

    // Hilfe-Dialog rechts, btn-info
    ->addModalElement('Hilfe', MForm::factory()
        ->addAlertInfo('Hinweistexte können hier strukturiert dargestellt werden.')
    , 'btn-info', 'right')
;
echo $mform->show();
```

### Modal im Repeater

Das Modal funktioniert auch innerhalb eines Repeaters – Einstellungen je Zeile:

```php
<?php
use FriendsOfRedaxo\MForm;

$rowForm = MForm::factory()
    ->addTextField('title', ['label' => 'Titel'])
    ->addTextAreaField('text', ['label' => 'Text'])
    ->addModalElement('Zeilen-Einstellungen', MForm::factory()
        ->addSelectField('bg', [
            '' => '– Standard –',
            'bg-light' => 'Hellgrau',
            'bg-dark text-white' => 'Dunkel',
        ], ['label' => 'Hintergrund'])
        ->addCheckboxField('fullwidth', [1 => 'Volle Breite'], ['label' => 'Layout'])
    , 'btn-default', 'center');  // Button-Ausrichtung: 'left' | 'center' | 'right'

$mform = MForm::factory()
    ->addFlexRepeaterElement(1, $rowForm, ['label' => 'Zeilen', 'btn_text' => 'Zeile hinzufügen']);
echo $mform->show();
```

> **Hinweis – Repeater im Modal:** Ein einfacher Repeater (Text, Select, Checkbox) kann innerhalb eines Modals verwendet werden. Felder mit JavaScript-Initialisierung beim DOM-ready – insbesondere **TinyMCE** und **MarkdownEditor** – funktionieren im Modal jedoch nicht zuverlässig, da sie sich vor dem ersten Öffnen initialisieren und das Modal zu diesem Zeitpunkt noch nicht sichtbar ist. Solche Felder gehören daher nicht in ein Modal.

## showWrapper / setShowWrapper

Standardmäßig rendert MForm ein umschließendes `<div class="mform-wrapper">` um das gesamte Formular. Mit `setShowWrapper(false)` kann dieser Wrapper entfernt werden – nützlich, wenn MForm-Ausgabe in ein eigenes Layout-Container-Element eingebettet wird.

Alle Container-Elemente (`addFieldsetArea`, `addCollapseElement`, `addTabElement`, `addColumnElement`, `addInlineElement`, `addForm`, `addRepeaterElement`) akzeptieren `showWrapper` ebenfalls als letzten bool-Parameter.

```php
<?php
use FriendsOfRedaxo\MForm;

// Globalen Wrapper deaktivieren
$mform = MForm::factory()
    ->setShowWrapper(false)
    ->addTextField(1, ['label' => 'Titel'])
    ->addTextAreaField(2, ['label' => 'Text']);

echo $mform->show();

// showWrapper nur für einen einzelnen Fieldset-Bereich deaktivieren
echo MForm::factory()
    ->addFieldsetArea(
        'Mein Bereich',
        MForm::factory()->addTextField(3, ['label' => 'Wert']),
        [],    // attributes
        false, // parse
        false  // showWrapper = false → kein Wrapper-Div um den Fieldset
    )
    ->show();
```
