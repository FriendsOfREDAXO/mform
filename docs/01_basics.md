# MForm vereinfacht Moduleingaben

Verwende MForm-Elemente in deinem Modul, indem du diese Beispiele kopierst und anpasst.

## Verfügbarkeit

| Methode | Klassisches Modul | `rex_form` | YForm |
|---|---|---|---|
| `addTextField` | ja | ja | ja |
| `addTextAreaField` | ja | ja | ja |
| `addHiddenField` | ja | ja | ja |
| `addSelectField` | ja | ja | ja |
| `addMultiSelectField` | ja | ja | ja |
| `addCheckboxField` | ja | ja | ja |
| `addRadioField` | ja | ja | ja |

## Text-Input- und Hidden-Elemente

Rendert Text- und Eingabe-Elemente wie `<input type="text">`, `<textarea>` oder `<input type="hidden">`.

> **Tipp:** Weiter unten folgen Beispiele für weitere HTML5-Elemente.

**Modul-Eingabe**

```php
<?php
use FriendsOfRedaxo\MForm;
// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Input Text elements', MForm::factory() // init new mform
        // add some text fields
        ->addTextField("1.0", ['label' => 'Input Text']) // use string for x.0 json values
        ->addTextField(1.2, ['label' => 'With options and Full width', 'full' => true])
            ->setOptions([1=>'option1',2=>'option2'])
    )
    // add second fieldset area
    ->addFieldsetArea('Textarea elements', MForm::factory() // init new mform
        ->addTextAreaField(1.3, ['label' => 'Textarea'])
        ->addTextAreaField(1.4, ['label' => 'Full width'])
            ->setFull() // or ->addTextAreaField(1.4, ['label' => 'Full width', 'full' => true])
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
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

dump(MFormOutputHelper::values(1));
dump(MFormOutputHelper::values(2));
```

## Checkbox und Radio-Elemente

Rendert `<input type="checkbox">` oder `input type="radio">`-Elemente.

**Modul-Eingabe**

```php
<?php
use FriendsOfRedaxo\MForm;
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

**Varianten**

addRadioImgField
```php
<?php
use FriendsOfRedaxo\MForm;

// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Radio Image Field', MForm::factory()
        // add radio image field
        ->addRadioImgField(4, [
            'layout1' => ['img' => '../theme/public/assets/backend/img/layout1.svg', 'label' => 'Layout 1'],
            'layout2' => ['img' => '../theme/public/assets/backend/img/layout2.svg', 'label' => 'Layout 2'],
            'layout3' => ['img' => '../theme/public/assets/backend/img/layout3.svg', 'label' => 'Layout 3']
        ], ['label' => 'Choose Layout'])
    );

// parse form
echo $mform->show();
```

addRadioIconField
```php
<?php
use FriendsOfRedaxo\MForm;

// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Radio Icon Field', MForm::factory()
        // add radio icon field
        ->addRadioIconField(5, [
            'grid' => ['icon' => 'fa fa-th', 'label' => 'Grid View'],
            'list' => ['icon' => 'fa fa-list', 'label' => 'List View'],
            'fullscreen' => ['icon' => 'fa fa-expand', 'label' => 'Fullscreen']
        ], ['label' => 'Choose Display Mode'])
    );

// parse form
echo $mform->show();
```

addImgPickerField (ab 10.1)

Kompakt wie eine Auswahlliste: Im Formular steht nur die gewählte Option mit kleinem Vorschaubild. Ein Klick öffnet ein Fenster mit allen Optionen als Bildkacheln – praktisch, wenn `addRadioImgField()` zu viel Platz braucht, etwa für die Typ-Auswahl in einem Repeater-Item. Die Optionen haben dieselbe Struktur wie bei `addRadioImgField()` (`img` + `label`), dazu optional `description` (kurzer Text unter dem Namen) oder `icon` (Font-Icon statt Bild).

Gespeichert wird wie bei `addSelectField()` der Array-Key. Im Hintergrund bleibt ein `<select>` im Formular, deshalb funktionieren `setVisibleIf()`, Conditional Fieldsets, der Flex-Repeater und Kopieren/Einfügen unverändert. Bedienung per Tastatur: Pfeiltasten (im Raster), Pos1/Ende, Enter oder Leertaste wählt, Esc schließt.

```php
<?php
use FriendsOfRedaxo\MForm;

// Vorschaubilder: Bild-URL (z. B. aus dem Medienpool) oder ein SVG als data-URI
$textPreview = rex_url::media('preview-text.svg');
$imagePreview = 'data:image/svg+xml;base64,' . base64_encode(
    '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 120 80"><rect width="120" height="80" fill="#fff"/>'
    . '<rect x="10" y="14" width="46" height="36" fill="#9fc3e3"/><rect x="64" y="18" width="44" height="5" fill="#68727d"/></svg>'
);

$types = [
    'text'  => ['img' => $textPreview,  'label' => 'Text',        'description' => 'Überschrift und Fließtext'],
    'image' => ['img' => $imagePreview, 'label' => 'Bild + Text', 'description' => 'Bild links, Text rechts'],
    'video' => ['icon' => 'rex-icon fa-film', 'label' => 'Video', 'description' => 'YouTube oder MP4'],
];

echo MForm::factory()
    ->addImgPickerField(1, $types, ['label' => 'Inhaltstyp'], 'text')
    ->addMediaField(2, ['label' => 'Bild'])
        ->setVisibleIf(1, '=', 'image')
    ->show();
```

Ausgabe wie bei einem Select: `'REX_VALUE[id=1]'` bzw. im Repeater `$item['type']`. Demo: *MForm → Demo → Basis → Bild-Picker*.

Der Picker startet bei `rex:ready` von selbst, auch in neuen Repeater-Items. Wer Felder per eigenem Skript nachlädt, ruft `window.MFormImgPicker.init(container)` auf.

addRadioColorField
```php
<?php
use FriendsOfRedaxo\MForm;

// init mform
$mform = MForm::factory()
    // add fieldset area
    ->addFieldsetArea('Radio Color Field', MForm::factory()
        // add radio color field
        ->addRadioColorField(6, [
            'red' => ['color' => '#ff0000', 'label' => 'Red'],
            'green' => ['color' => '#00ff00', 'label' => 'Green'],
            'blue' => ['color' => '#0000ff', 'label' => 'Blue'],
            'transparent' => ['color' => 'transparent', 'label' => 'Transparent']
        ], ['label' => 'Choose Color'])
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
use FriendsOfRedaxo\MForm;
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

### Tooltip-Icon: Default und Override

Wenn bei `setTooltipInfo()` kein Icon gesetzt wird, nutzt MForm standardmäßig `fa-info-circle`.

```php
<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addTextField("1.0", ['label' => 'Titel'])
        // Default-Icon: fa-info-circle
        ->setTooltipInfo('Standard-Hinweis ohne Icon-Override')

    ->addTextField(1.1, ['label' => 'Untertitel'])
        // Eigenes Icon explizit gesetzt
        ->setTooltipInfo('Hinweis mit Fragezeichen-Icon', 'fa-question-circle');

echo $mform->show();
```

## Select- und Multiselect-Elemente

Rendert `<select>`- und `<select multiple>`-Elemente.

**Modul-Eingabe**

```php
<?php
use FriendsOfRedaxo\MForm;
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
use FriendsOfRedaxo\MForm\Utils\MFormOutputHelper;

dump(MFormOutputHelper::values(1));
dump(MFormOutputHelper::values(2));
```

## Weitere HTML5-Elemente

Seit MForm 10 gibt es für die gängigen HTML5-Typen eigene Methoden. `min`, `max`, `step` und `pattern` kommen über die Attribute, `addRangeField()` zeigt den aktuellen Wert neben dem Regler an:

```php
<?php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addNumberField(1, ['label' => 'Menge', 'min' => 1, 'max' => 99, 'step' => 1], '1')
    ->addRangeField(2, ['label' => 'Deckkraft', 'min' => 0, 'max' => 100], '80')
    ->addDateField(3, ['label' => 'Beginn'])
    ->addDateTimeField(4, ['label' => 'Termin'])
    ->addTimeField(5, ['label' => 'Uhrzeit'])
    ->addEmailField(6, ['label' => 'E-Mail'])
    ->addColorField(7, ['label' => 'Farbe'], '#2f77bc')
    ->show();
```

Alle anderen Typen (`url`, `tel`, `search`, `month`, `week`, …) laufen weiter über `addInputField($type, $id, …)`.

### Zusätzliche Feldtypen über `addInputField()`

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
        // input datetime-local
        ->addInputField("datetime-local", 1.9, ['label'=>'Datetime-local field']) // Datum und Uhrzeit (ohne Zeitzone)
        // input date
        ->addInputField("date", "1.10", ['label'=>'Date field']) // Datum
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
use FriendsOfRedaxo\MForm;
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

### Notice / Hinweistext

Fügt einen Hinweistext (Help-Block) unterhalb eines Feldes ein. Funktioniert bei allen Eingabe-Feldern über das `notice`-Attribut.

```php
<?php
use FriendsOfRedaxo\MForm;

$mform = MForm::factory()
    ->addFieldsetArea('Hinweistexte', MForm::factory()
        ->addTextField(1, [
            'label'  => 'URL',
            'notice' => 'Bitte die vollständige URL inkl. https:// angeben.',
        ])
        ->addMediaField(2, ['label' => 'Bild', 'notice' => 'Empfohlene Größe: 1920 × 800 px'])
        ->addSelectField(3, ['de' => 'Deutsch', 'en' => 'Englisch'], [
            'label'  => 'Sprache',
            'notice' => 'Bestimmt die Ausgabe-Sprache des Moduls.',
        ])
    );

echo $mform->show();
```

### Datalists und Optionen

Setzt Datalists und Optionen für verschiedene Input-Elemente.

```php
<?php
use FriendsOfRedaxo\MForm;
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
        ->addInputField("datetime-local", 3, ['label'=>'Datetime-local field'])
            ->setOptions(["Santa Visit"=>"2012-12-24T23:59", "Chrismas party"=>"2012-12-25T18:00", "Happy New Year"=>"2013-01-01T00:00", "2012-12-30T00:00"]) // to add datalist use set options
        // input time with datalist
        ->addInputField("time", 4, ['label'=>'Time field'])
            ->setOption("00:00", "Midnight")
            ->setOption("06:00", 2)
            ->setOption("12:00", "Noon")
            ->setOption("18:00", 4)
    );
// parse form
echo $mform->show();
```
