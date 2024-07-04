# MForm - REDAXO Addon

![Poster](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform8.png?raw=true)

MForm facilitates the creation of REDAXO module inputs. With MForm, you can not only create forms, but also visually design them exactly to your own specifications thanks to flexible templates. It allows you to create all standard REDAXO form elements and includes several extra widgets that can be easily integrated into modules.

**But it doesn’t stop there!** 
MForm also enhances YForm and rex_form with additional widgets. Need a custom link field or a chic image list? No problem, MForm has you covered.

## New in Version 8
The highlight of the latest version? The brand-new Form Repeater! This feature replaces the old MBlock AddOn and offers the ability to not only repeat form elements but also nest them at multiple levels – something that was not possible with MBlock. This allows for the construction of even more complex forms.

The included **demo collection** allows for immediate testing of module codes. Modules can be directly installed and tested. The codes are all commented.
And additionally, there is a comprehensive documentation 📒.

## Features

### Basic Functionalities
- **Creation of module inputs via PHP**: The foundation for working with MForm.
- **Multi-column forms**: Layout options for structuring the forms.
- **Inline form elements**: For compact form design.
- **HTML5 form elements**: Utilization of modern web standards.
- **Datalists**: For enhanced input options in forms.

### Advanced Design and Interactivity
- **Custom widgets for linking (including Yform) and images**: Special widgets for frequently needed functions.
- **Factory that allows easy outsourcing of form parts**: Simplifies the reuse of form components.
- **Collapse, Tabs, Accordions**: Elements for designing dynamic, interactive forms.
- **Wrapper elements via Checkbox, Radio, or Select controlled collapse elements**: Provides interactive controls for user guidance.
- **Form output customizable via fragments**: Allows for flexible design of the form presentation.

### Special Features
- **Integrated Form Repeater**: Replaces MBlock and allows nested form elements.
- **REDAXO JSON Value Utilization**: Integration of REDAXO-specific data structures.
- **SQL fields**: Direct integration of database queries.
- **Continuous MBlock Compatibility**: Ensures compatibility with existing MBlock installations.
- **Module examples for direct installation**: Provides ready-to-use templates for various use cases.

## Form Repeater

The Form Repeater allows dynamic repetition of form elements while realizing nesting at multiple levels.

### Migration from MBlock to MForm 8 

The new repeater is only compatible with MBlock to a limited extent.

It does not currently work during a migration: 

- CustomLinkField // Converter: https://friendsofredaxo.github.io/tricks/addons/mform/custom_link_converter
- addMediaListField
- addLinkListField


***MBlock Module*** 

```php
// Base ID for managing form elements
$id = 1;

// Initialize MForm
$mform = new MForm();

// Add a fieldset
$mform->addFieldsetArea('Team member');

// Add a text field, referring dynamically to a JSON format
$mform->addTextField("$id.0.name", array('label' => 'Name'));

// Add a media field saved by MBlock in JSON
$mform->addMediaField(1, array('label' => 'Avatar'));

// Output the form with MBlock, which allows dynamic handling of blocks
echo MBlock::show($id, $mform->show(), array('min' => 2, 'max' => 4));
```

***The same module in MForm 8*** 

To determine the necessary field keys, a dump might be needed beforehand. 
Note: From the original MBlock Mediafield 1, it becomes: `'REX_MEDIA_1'`

```php
use FriendsOfRedaxo\MForm;

// Repeater initialization ID with the base ID of the original MBlock section
$id = 1;

// Create a new MForm instance with the factory method and directly integrate a repeater
echo MForm::factory()
    ->addRepeaterElement(
        $id, 
        MForm::factory()
            ->addFieldsetArea('Team member', 
                MForm::factory()
                    ->addTextField('name', ['label' => 'Name'])
                    ->addMediaField('REX_MEDIA_1', ['label' => 'Avatar'])
            ),
        true, 
        true, 
        ['min' => 2, 'max' => 4]
    )
    ->show();
```



## Installation

MForm can be directly installed via the Redaxo Installer. [MForm Redaxo Addon Page](http://www.redaxo.org/de/download/addons/?addon_id=967&searchtxt=mform&cat_id=-1)

1. Log in to REDAXO
2. In the backend under "Installer > Download new", search for "MForm" and click on "view" under "Function"
3. In the current version list, click "download" under "Function"
4. Install and activate MForm under "AddOns"

## Output


MForm utilizes REDAXO variables provided by REDAXO, either as classic or JSON values.
For more information, see the [REDAXO Documentation](https://www.redaxo.org/doku/main/redaxo-variablen).

## License

MForm is licensed under the [MIT License](LICENSE.md).

## Changelog

See [CHANGELOG.md](https://github.com/FriendsOfREDAXO/mform/blob/master/CHANGELOG.md)

## Author

**Friends Of REDAXO**

- <http://www.redaxo.org>
- <https://github.com/FriendsOfREDAXO>

## Credits

**Project Lead**

[Joachim Dörr](https://github.com/joachimdoerr)

**2nd Maintainer**

[skerbis](https://github.com/skerbis)

**Mform Repeater**

[Thorben eaCe](https://github.com/eaCe)

**Docs & Testing**

[alexplusde](https://github.com/alexplusde)
