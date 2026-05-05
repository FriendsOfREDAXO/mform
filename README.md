# MForm - REDAXO Addon

![Poster](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform8.png?raw=true?2)

MForm facilitates the creation of REDAXO module inputs. With MForm, you can not only create forms, but also visually design them exactly to your own specifications thanks to flexible templates. It allows you to create all standard REDAXO form elements and includes several extra widgets that can be easily integrated into modules.

**But it doesn’t stop there!** 
MForm also enhances YForm and rex_form with additional widgets. Need a custom link field or a chic image list? No problem, MForm has you covered.

## New in Version 9
Version 9 focuses on a more robust and editor-friendly form workflow.

- New Flex Repeater runtime with stable behavior in dynamic backend contexts
- New item activation state in repeater (eye icon): keep entries editable but exclude them from frontend output
  - Item status is now visible directly in the header via a filled status dot (green = active, red = offline)
- **Copy/Paste for Flex Repeater** – `copy_paste => true` on `addRepeaterElement()`: copy a single item to session storage, paste it as a new entry at the bottom
- Better TinyMCE compatibility in repeater actions (add/move/sort/remove)
- New Linklist/Medialist repeater widget with robust popup synchronization
- Medialist widget now supports a built-in view switch (list/grid) via toolbar toggle
- New `addCustomLinkMultipleField(...)` API – repeater-based multi-link field; single format stays unchanged
- New Conditional Fields Builder API via `addConditionalFieldsetArea(...)`
- **New YForm Value Types** provided by mform:
  - `custom_link` – now supports `anchor: 0` to hide the anchor button; bug in classic template fixed
  - `custom_link_multi` – multiple links per YForm field, stored as JSON array
- **New helper `MFormRepeaterHelper::decode()`** – decode repeater values without offline items in one call
- Extended demo collection (Conditional Fields, Copy/Paste Repeater)
- Expanded documentation for repeater output filtering and conditional field usage

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

```php
use FriendsOfRedaxo\MForm;

echo MForm::factory()
    ->addRepeaterElement(
        1,
        MForm::factory()
            ->addFieldsetArea('Team member', MForm::factory()
                ->addTextField('name', ['label' => 'Name'])
                ->addMediaField('image', ['label' => 'Avatar'])
            ),
        true,
        true,
        ['min' => 1, 'max' => 10]
    )
    ->show();
```

> **MBlock migration:** See [docs/08_mblock_migration.md](docs/08_mblock_migration.md) for a step-by-step migration to MForm 9.

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
