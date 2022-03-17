# MForm - REDAXO Addon for better input forms

![Screenshot](https://github.com/FriendsOfREDAXO/mform/blob/assets/screen_mform7.png?raw=true)

MForm is a REDAXO Addon, which makes the creation of module input forms much easier. MForm uses templates which enable the administrator to customize the module appearance. MForm provides all common module input form elements and additonal widgets which can be easily integrated. MForm also extends **YForm** and **rex_form** with additional widgets, e.g. a custom link field and image list for galleries. 

The included **Demo library** allows you to try out module codes immediately. Modules can be installed and tested directly. The codes are all annotated. 

## Features

- Creation of module inputs via PHP
- Output of forms customizable via fragments
- Custom widgets for linking (also YForm) and images
- Factory that allows form parts to be easily swapped out 
- REDAXO JSON value handling
- Multi-column forms
- Inline form elements
- Module examples for direct installation
- HMTL5 form elements 
- SQL fields
- Collapse, Tabs 
- Accordions Wrapper elements Via Checkbox 
- Radio or Select controllable collapse elements
- Full MBlock compatibility
- Datalists 

**Notes**

* The MForm form builder is only designed to generate REDAXO module input forms!
* Currently the imagelist widget is not mblock compatible


## Installation:

MForm can be installed directly via the Redaxo installer. [MForm Redaxo Addon Page](http://www.redaxo.org/de/download/addons/?addon_id=967&searchtxt=mform&cat_id=-1)

1. log in to REDAXO
2. in backend under "Installer > Download new" search "MForm" and under "Function" click "view"
3. click on "download" in the list of the current version under "function
4. install and activate MForm under "AddOns"


## Usage

MForm must be notated as PHP code in the module input of a REDAXO module.


### Instancing 

```php
// instantiate
$MForm = MForm::factory();
```

Any number of MForm forms can be created, which can also be instantiated directly as element properties.

```php
// instantiate
$MForm = MForm::factory() // init 
    ->addFieldsetArea('My fieldset', MForm::factory() // use fieldset method and init new mform instance 
            ->addTextField(1, ['label' => 'My input']) // add text field with rex_value_id 1 and label attribute
    );
```

### Form elements

The main form elements provided by MForm are added by methods.

```php
$MForm = MForm::factory()
    ->addHeadline("Headline") // add headline
    ->addTextField(1, ['label' => 'Input', 'style' => 'width:200px']); // add text field with rex_value_id 1
```

All MForm methods expect optional attributes, parameters and options. These can also be subsequently assigned to the element by setters.

```php
// add text field
$MForm = MForm::factory()
    ->addTextField(1) // add text field with rex_value_id 1
    ->setLabel('Text Field') 
    ->setAttributes(['style' => 'width:200px', 'class' => 'test-field']);
```
The `REX_VALUE-Key` must be passed to each form input method as a mandatory field. Informational elements do not need an ID.

##### Full JSON value support

MForm supports `REX_VALUE-ARRAYs` so there is no longer any `REX_VALUE` limitation. Note that each x.0 key must be passed as a string.

```php
// add text field
$MForm = MForm::factory()
    ->addTextField("1.0")
    ->addTextField(1.1)
    ->addTextField("1.2.Titel");
```

### Compose form

To generate the composed form, the `show` method must be used.

```php
 // create output
echo $MForm->show();

// without var
echo MForm::factory()
    ->addTextField(1, ['label' => 'Input', 'style' => 'width:200px']) // add text field with rex_value_id 1
    ->show();
```

### Element methods

MForm provides the following element methods:

* Structural wrapper elements
  * `addFieldsetArea`
  * `addCollapseElement`
  * `addAccordionElement`
  * `addTabElement`
  * `addColumnElement`
  * `addInlineElement`
* Text input and hidden elements
  * `addTextField`
  * `addHiddenField`
  * `addTextAreaField`
  * `addTextReadOnlyField`
  * `addTextAreaReadOnlyField`
* Select elements
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox and radio elements
  * `addCheckboxField`
  * `addRadioField`
* Informal elements
  * `addHtml`
  * `addHeadline`
  * `addDescription`
  * `addAlert`
  * `addAlertDanger`, `addAlertError`
  * `addAlertInfo`
  * `addAlertSuccess`
  * `addAlertWarning`
* System button elements
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`
* Custom elements 
  * `addCustomLinkField`
  * `addImagelistField`
  * `addInputField`
* Special 'setter' methods
  * `setAttribute`
  * `setAttributes`
  * `setCategory`
  * `setCollapseInfo`
  * `setDefaultValue`
  * `setDisableOption`
  * `setDisableOptions`
  * `setFormItemColClass`
  * `setFull`
  * `setLabel`
  * `setLabelColClass`
  * `setMultiple`
  * `setOption`
  * `setOptions`
  * `setParameter`
  * `setParameters`
  * `setPlaceholder`
  * `setSize`
  * `setSqlOptions`
  * `setTabIcon`
  * `setToggleOptions`
  * `setTooltipInfo`

## Output 

MForm uses the REDAXO variables provided by REDAXO. Either as classic or as JSON values. 
See the [REDAXO doc / german](https://www.redaxo.org/doku/main/redaxo-variablen) for information.

## License

MForm is licensed under the [MIT License](LICENSE.md).

## Changelog

see [CHANGELOG.md](https://github.com/FriendsOfREDAXO/mform/blob/master/CHANGELOG.md)

## Author

**Friends Of REDAXO**

* http://www.redaxo.org
* https://github.com/FriendsOfREDAXO

**Project lead**

[Joachim Dörr](https://github.com/joachimdoerr)

