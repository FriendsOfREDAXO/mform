# Wrapper- und Layout-Elemente

Diese Moduleingabe-Beispiele zeigen, wie man mittels der MForm Wrapper-Elemente Input-Formulare sinnvoll layouten und interaktiv aufbereitet.

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
