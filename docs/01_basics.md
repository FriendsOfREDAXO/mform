# MForm vereinfacht Moduleingaben

Verwendene MForm-Elemente in deinem Modul, indem du diese Beispiele kopierst und anpasst.

## Text-Input- und Hidden-Elemente

Rendert Text- und Eingabe-Elemente wie `<input type="text">`, `<textarea>` oder `<input type="hidden">`.

> **Tipp:** Weiter unten folgen Beispiele für weitere HTML5-Elemente.

**Modul-Eingabe**

```php
<?php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Input Text elements', MForm::factory() // init new mform
        // add some text fields
        ->addTextField(1.0, ['label' => 'Input Text'])
        ->addTextField(1.2, ['label' => 'With options and Full width', 'full' => true])
            ->setOptions([1=>'option1',2=>'option2'])
    )
    // add second fieldset area
    ->addFieldsetArea('Textarea elements', MForm::factory() // init new mform
        ->addTextAreaField(1.3, ['label' => 'Textarea'])
        ->addTextAreaField(1.4, ['label' => 'Full width'])
            ->setFull() // or ->addTextAreaField(1.2,['full' => true])
    )
    // add third fieldset area
    ->addFieldsetArea('Readonly text elements', MForm::factory() // use mform factory
        // add some readonly text fields
        ->addTextReadOnlyField("2.0", 'string readonly', ['label' => 'Readonly Input Text'])
        ->addTextAreaReadOnlyField(2.1, 'string readonly', ['label' => 'Readonly Textarea'])
    );
// parse form
echo $mform->show();
```

**Modul-Ausgabe**

```php
<?php
dump(rex_var::toArray('REX_VALUE[id=1]'));
dump(rex_var::toArray('REX_VALUE[id=2]'));
```

## Checkbox und Radio-Elmente

Rendert `<input type="checkbox">` oder `input type="radio">`-Elemente.

**Modul-Eingabe**

```php
<?php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Checkbox element', MForm::factory()
        // some checkbox elements
        ->addCheckboxField(1, [1 => 'test-1'], ['label' => 'Checkbox'])
        ->addToggleCheckboxField(3, [1 => 'Toggle test-1'], ['label' => 'Toggle Checkbox'])
    )
    // add second fieldset area
    ->addFieldsetArea('Radio buttons element', MForm::factory()
        // radio element
        ->addRadioField(2, [1 => 'test-1', 2 => 'test-2'], ['label' => 'Radio Buttons'])
    );
// parse form
echo $mform->show();
```

**Modul-Ausgabe**

```php
<?php
dump('REX_VALUE[id=1]');
dump('REX_VALUE[id=2]');
dump('REX_VALUE[id=3]');
```

## Strukturelle Info-Elemente

Rendert Überschriften, Alerts und andere HTML-Elemente.

**Modul-Eingabe**

```php
<?php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Fieldset Element', MForm::factory()
        // headline, description and some other elements
        ->addHeadline('MForm Demo Headline')
        ->addDescription('Mform Demo Description for any descriptions texts in your modul input formular.')
        ->addHtml('<b>HTML <i>Text</i></b><br>')
    )
    // add second fieldset area
    ->addFieldsetArea('Alert Messages', MForm::factory()
        ->addAlertInfo('Info Alert Message')
        ->addAlertDanger('Danger Alert Message')
        ->addAlertSuccess('Success Alert Message')
        ->addAlertWarning('Warning Alert Message')
        ->addAlertError('Error Alert Message')
    )
    // add third fieldset area
    ->addFieldsetArea('', MForm::factory()
        // toogle checkbox with tooltip
        ->addToggleCheckboxField(2, [1 => 'Test-Checkbox'], ['label' => 'Checkbox'])
        ->setTooltipInfo('Tooltip Test-Checkbox Label.', 'fa-question-circle')
    );
// parse form
echo $mform->show();
```

## Select- und Multiselect-Elemente

Rendert `<select>`- und `<select multiple>`-Elemente.

**Modul-Eingabe**

```php
<?php
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Select elements', MForm::factory()
        // some select fields
        ->addSelectField("1.0", [1 => 'test-1', 2 => 'test-2', 3 => 'test-3', 4 => 'test-4'], ['label' => 'Select optgroup'])
        ->addSelectField("1.1", ['group 1' => [1 => 'test-1', 2 => 'test-2'], 'group 2' => [3 => 'test-3', 4 => 'test-4']], ['label' => 'Select optgroup'])
    )
    // add second fieldset area
    ->addFieldsetArea('Multiselect elements', MForm::factory()
        // some multiple selects fields
        ->addMultiSelectField("2.0", [1 => 'test-1', 2 => 'test-2', 3 => 'test-3', 4 => 'test-4'], ['label' => 'Select optgroup'])
        ->addMultiSelectField("2.1", ['group 1' => [1 => 'test-1', 2 => 'test-2'], 'group 2' => [3 => 'test-3', 4 => 'test-4']], ['label' => 'Select optgroup'])
    );
// parse form
echo $mform->show();
```

**Modul-Ausgabe**

```php
<?php
dump(rex_var::toArray('REX_VALUE[id=1]'));
dump(rex_var::toArray('REX_VALUE[id=2]'));
```

## Weitere HTML5-Elemente

Erweitert MForm-Elemente um weitere HTML5-Elemente wie `type="email"`, `type="url"` oder `type="tel"`.

### Zusätzliche Feldtypen

```php
<?php
// show form elements
echo MForm::factory() // init mform
    // add fieldset area
    ->addFieldsetArea('HTML5 input elements', MForm::factory()
        // input color
        ->addInputField("color", "1.0", ['label'=>'Color field']) // use string for x.0 json values
        // input email
        ->addInputField("email", 1.1, ['label'=>'Email field'])
        // input url
        ->addInputField("url", 1.2, ['label'=>'URL field'])
        // input tel
        ->addInputField("tel", 1.3, ['label'=>'Tel field'])
        // input search
        ->addInputField("search", 1.4, ['label'=>'Search field'])
        // input number
        ->addInputField("number", 1.5, ['label'=>'Number field'])
        // input range
        ->addInputField("range", 1.6, ['label'=>'Range field'])
        // input range
        ->addInputField("range", 1.7, ['label'=>'Range field with datalist'])
            ->setOptions(array(1,"-20", 30, "-30"))
        // input datetime
        ->addInputField("datetime", 1.8, ['label'=>'Datetime field']) // Datum und Uhrzeit (mit Zeitzone)
        // input datetime-local
        ->addInputField("datetime-local", 1.9, ['label'=>'Datetime-local field']) // Datum und Uhrzeit (ohne Zeitzone)
        // input date
        ->addInputField("date", 1.10, ['label'=>'Date field']) // Datum
        // input time
        ->addInputField("time", 1.11, ['label'=>'Time field']) // Uhrzeit
        // input month
        ->addInputField("month", 1.12, ['label'=>'Month field']) // Monat
        // input week
        ->addInputField("week", 1.13, ['label'=>'Week field']) // Kalenderwoche
    )
    // parse form
    ->show();
```

### Placeholder

Fügt ein Platzhalter-Attribut zu einem Textfeld hinzu.

```php
<?php
// init mform
$mform = MForm::factory()
    // fieldset with placeholder
    ->addFieldsetArea('Placeholder', MForm::factory()
        // text input with placeholder
        ->addTextField("1.0", array('label'=>'Text input', 'placeholder' => 'Test Placeholder'))
        // textinput with placeholder
        ->addTextField(1.1, array('label'=>'Text input'))
            ->setPlaceholder('Test Placeholder')
    );
// parse form
echo $mform->show();
```

### Datalists und Optionen

Setzt Datalists und Optionen für verschiedene Input-Elemente.

```php
<?php
// init mform
$mform = MForm::factory()
    // fieldset
    ->addFieldsetArea('Form elements with datalist', MForm::factory()
        // text with datalist
        ->addTextField(1, ['label'=>'Text input'])
            ->setOptions(['Apple', 'Orange', 'Peach', 'Melon', 'Strawberry'])
        // input range width datalist
        ->addInputField("range", 2, ['label'=>'Range field with datalist'])
            ->setAttribute("max",60)
            ->setAttribute("min",20)
            ->setOptions([10,20,30,40,50]) // to add datalist use set options
        // input datetime locale with datalist
        ->addInputField("datetime-local", 2, ['label'=>'Datetime-local field'])
            ->setOptions(["Santa Visit"=>"2012-12-24T23:59", "Chrismas party"=>"2012-12-25T18:00", "Happy New Year"=>"2013-01-01T00:00", "2012-12-30T00:00"]) // to add datalist use set options
        // input time with datalist
        ->addInputField("time", 3, ['label'=>'Time field'])
            ->setOption("00:00", "Midnight")
            ->setOption("06:00", 2)
            ->setOption("12:00", "Noon")
            ->setOption("18:00", 4)
    );
// parse form
echo $mform->show();
```
