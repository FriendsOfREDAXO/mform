# MForm - REDAXO Addon für Modul-Input-Formulare

## Version 8-beta7

- IMPORTANT: breaking namespace change -> from `MForm` to `FriendsOfRedaxo\MForm`
- new docs added - thanks @alxndr-w
- added install button to collapse title for example modules
- fixed multiple select default value issue
- fixed select '0' value issue
- ensured full option functionality for description  

## Version 8-beta6

- fix button style, ensure that buttons ever will display, added potential disable style for buttons
- min and max count of blocks for repeater
- rename `addRadioImgInlineField` to `addRadioImgField` and `addRadioColorInlineField` to `addRadioColorField`

## Version 8-beta5

- added delete confirmation option for repeater group and fields
- added radio image / color inline field
- default value support for repeater added

## Version 8-beta4

- fix repeater parentId issue
- added handling for cke5 move preparation
- fix :id label issue
- added the option to hide the repeater if empty
- css for repeater exchanged

## Version 8-beta3

- added repeater examples
- fix :id, move addInputs method to elements class

## Version 8-beta2

- add recursion for repeater mform subform obj structure

## Version 8-beta1

- change mform css filename
- remove unused $inline variable
- add inputs for default form input forms
- alpine repeater with 2 level support
- remove dynamic creation of property in fragments

## Version 7.2.2

- fix PHP 8.1 deprecation warnings
- prevent module action when Gridblock addon is used @skerbis

## Version 7.2.1

- make mform manipulable

## Version 7.2.0

- Stretch height of ImageList

## Version 7.1.2

- PRESAVE-Action: Die REX_VALUES werden nach einer PRESAVE Aktion aus dem $_POST geladen.
- Dadurch sind Validierungen ohne Neueingabe des Contents jetzt möglich. @skerbis

## Version 7.1.1

- use mblock:change event to reinit mform elements

## Version 7.1.0

- remove form-group wrapper for hidden input fields
- fix collapse accordion aria-expanded click issue
- added the selectpicker class by default, to remove it you have use the class `none-selectpicker` on your select element
- added mblock compatibility for mform usage of with the selectpicker
- fix some small issues to get ability for combined and nested wrapper element usage  

## Version 7.0.0

- added column element for some form input column elements
- removed element fragments and added stuff to wrapper fragment
- removed author email address
- wrapper fragment changed, remove id handling in output and js for accordion, collapse and tab
- properties direction changed in `addAccordionField`, `addCollapseField`
- open properties added to `addTabField`
- added inline wrapper element `addInlineElement`
- minor css changes @skerbis
- add php 7.x compatibility
- add fragment files
- remove data themes and use fragments as theme templates
- add radio-, checkbox and select toggle options for collapse
- add radio-, checkbox and select toggle options for tabs
- create generic collapse, accordion and tab handling for mblock usage
- remove deprecated stuff
- remove default theme config form in addon page
- ytemplates moved from data to addon root path
- remove docs plugin and unused lang strings
- make example modules installable
- revised all example modules remove all .ini's and use instead of them .inc files
- add new wrapper example files
- validations removed
- changed screenshot @skerbis
- correcting readme @skerbis
- add english readme @sckerbis
- add readme to backend pages @skerbis
- add changelog to backend navigation @skerbis 
- fix double quote issue @dtpop

### Migration to from v6.x.x to v7.x.x

1. Removed class methods:
   1. addEditorField
   2. addCke5Field
   3. addFieldset
   4. closeFieldset
   5. addTab
   6. closeTab
   7. addCollapse
   8. closeCollapse
   9. addAccordion
   10. closeAccordion
   11. isSerial
   12. setToggle
   13. setValidation
   14. setValidations

2. Renamed class methods:
   1. addOption => setOption 
   2. addAttribute => setAttribute
   4. disableOptions => setDisableOptions
   5. disableOption => setDisableOption
   6. addFieldsetField => addFieldsetArea
   7. addCollapseField => addCollapseElement
   8. addTabField => addTabElement
   9. addAccordionField => addAccordionElement
   10. addTooltipInfo => setTooltipInfo
   11. addCollapseInfo => setCollapseInfo
   12. addParameters => setParameters
   13. addParameter => setParameter

### Migration how do?

- `Call to undefined method MForm::addFieldset()` or `MForm::closeFieldset()`
  - Use `addFieldsetArea` like `MForm::factory()->addFieldsetArea('Label', MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addCollapse()` or `MForm::closeCollapse()`
    - Use `addCollapseElement` like `MForm::factory()->addCollapseElement('Collapse', MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addTab()` or `MForm::closeTab()`
    - Use `addTabElement` like `MForm::factory()->addTabElement('Tab', MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addAccordion()` or `MForm::closeAccordion()`
    - Use `addAccordionElement` like `MForm::factory()->addAccordionElement('Accordion' MForm::factory()->addTextField(1, ['label' => 'Text']));`
- `Call to undefined method MForm::addEditorField()` or `MForm::addCke5Field`
  - Use `addTextAreaField` with editor attributes like `$mform->addTextAreaField('1', ['class' => 'cke5-editor', 'data-lang' => \Cke5\Utils\Cke5Lang::getUserLang(), 'data-profile' => 'default']);`
- `Call to undefined method MForm::addOption()` or `MForm::addAttribute()` or `MForm::disableOptions()` or `MForm::disableOption()`
    - Check the list `Renamed class methods` and use the new method name instead of the old one
- `Call to undefined method MForm::setToggle()`
    - Use `addToggleCheckboxField` instead of `addCheckboxField` with `setToggle`

## Version 6.1.2

- Fix: UTF-8 encoding for arabic and other charsets in link and link-lists

## Version 6.1.1

- Adding return types for yform methods

## Version 6.1.0

- New: Dark-mode support for REDAXO >= 5.13 @schuer @skerbis

## Version 6.0.9 – 6.0.13

- Select fix. @dtpop, @IngoWinter, @skerbis, multiple issues fixed regarding single select- and multiselect-fields
- Allow callable @DanielWeitenauer
- new check for JSON Values 1.x.x


## Version 6.0.6

- fixed: delete all entries in imagelist @ynamite 
- fixed: wrong var prevents cusrtom classes on tabs @bitshiftersgmbh 

## Version 6.0.4

- Fixed missing external link in widget
- Some minor fixes
thx @lexplatt  @Hirbod

## Version 6.0.3
prepareCustomLink fixed


## Version 6.0.2
- readme style fixes @crydotsnake 
- remove .formcontrol on input fields type color @olien
- some validation methods changed and calls deleted @skerbis


## Version 6.0.1
- added some docs
- minor bugfixes


## Version 6.0.0

* use rex_factory_trait in MForm class
* added YForm Links in custom_link
* removed `parsley` validation @skerbis, you should use html validations: https://developer.mozilla.org/en-US/docs/Learn/Forms/Form_validation
* deprecated: `closeCollapse`, `closeTab`, `closeAccordion`
* change `addCollapse`, `addAccordion`, `addTab` functionality, use `addForm` to add content in this methods
* added `addForm` method
* added Media inUseCheck for media inside `custom_link` and `imagelist` in YForm
* added some styling
* added some Svensk översättning @interweave-media 
* added some English translation @ynamite
* added some docs @skerbis

### Breaking changes: 

The REX_CUSTOM_LINK Var now saves the data in a regular REX_VAR. So the usage of REX_CUSTOM_LINK is not backward compatible. You should move the values from Linklist to a value field. 

Parsley has been removed. AddValidation is functionless. 

removed `closeCollapse`, `closeTab`, `closeAccordion`
> Look at the new wrapper field examples


## Version 5.3

* added custom link as widget
* exchange custom link for yform and rex_form
* added image list widget for mfrom, rex_form and yform 

## Version 5.2.5

* add possibility to disable select options
* fix fieldset and grouping issues

## Version 5.2.4

* re-add special input types support

## Version 5.2.3

* removed tab history

## Version 5.2.2

* Parameter must be an array warning fix

## Version 5.2.1

* fixed: Media-Button Parameter type space
* Link-Title for Custom-Link Buttons added
* Attributes for media- and link-elements added, which allows validation via Parsley
* fixed: c14n html body wrapping removed

Changes: 

* now uses includeCurrentPageSubPath to show pages @christophboecker
* Cache buster will be added by rex core @staabm
* init.js simplified @staabm


## Version 5.2.0 pre-release

* Add Info Tooltip
* [Bootstrap Toggle Checkbox](http://www.bootstraptoggle.com) als neues Element `addToggleCheckboxField` hinzugefügt
```
$mform->addToggleCheckboxField('1.show_icons', [1 => ''], ['label' => 'Icon verwenden', 'label-col-class' => 'col-md-3', 'form-item-col-class' => 'col-md-9'], null, 1);
```
* `setLabelColClass` und `setFormItemColClass` hinzugefügt, ermöglicht das überschrieben der Standard "col-md-x" Classen
```
$mform->addTextField('2.0.title', ['label' => 'Titel'], ['empty']);
$mform->setLabelColClass('col-md-3');
$mform->setFormItemColClass('col-md-9');
```
* 4 Alert Message Elemente hinzugefügt
```
$mform->addAlertInfo('Heads up! This alert needs your attention, but it\'s not super important.');
$mform->addAlertSuccess('Well done! You successfully read this important alert message.');
$mform->addAlertDanger('Oh snap! Change a few things up and try submitting again.');
$mform->addAlertWarning("<strong>Warning!</strong> Better check yourself, you're not looking too good.");
```
* Collapse Panel für Formular elemente hinzugefügt, das steuern der Collapse über Checkboxen ist möglich
* Output helper class `MFormOutputHelper` bereit gestellt

## Version 5.1.0

* Javascript für Multipe Selects entfernt, dafür nötiges Hidden-Input ebenfalls entfernt. 
    * **Zu beachten bei Updates**:
        * Ein Hidden-Input Feld welches Komma-separiert die selected-options aufnimmt gibt es nicht mehr.
        * Multiple Selects werden künftig als JSON-String direkt im REX_VALUE gespeichert.
        * Dies Wirkt sich auf die Auswertung der REX_VALUES im Modul-Output aus.
        * Künftig muss für diese REX_VALUES `rex_var::toArray` genutzt werden um die JSON-Strings in Arrays zu decodieren.
        * Beim editieren alter REX_VALUES gehen keine zuordnungen verloren, beim erneuten Speichern wird im neuen Format gespeichert.
        * Im DB Column des REX_VALUES wird aus dem String `1,2` der JSON-String `["1","2"]`
* Docu Plugin hinzugefügt
    * Das alte MForm Github Wiki wurde in das Docu-Plugin übernommen
    * Alle Inhalte wurden überarbeitet
    * Thanks Alexander Walther, Paul Götz, Tim Filler
* Bootstrap Tabs integriert
* Selected und Checked haben einen Leerzeichen-Prefix erhalten.
* EN-Sprachdatei wurde übersetzt    
    * Thanks Thomas Skerbis, ynamite 
