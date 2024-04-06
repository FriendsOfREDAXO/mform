# Erweiterte Beispiele

Kniffe und Praxisbeispiele, wie MForm-Elemente an die jeweiligen Anforderungen angepasst werden können.

MForm stellt folgende Element-Methoden bereit:

- Strukturelle Wrapper-Elemente
  - `addFieldsetArea`
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
  - `addRadioField`
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
  - `addImagelistField`
  - `addInputField`
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
