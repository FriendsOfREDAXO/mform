# MForm - REDAXO Addon für Modul-Input-Formulare

### Version 6.1.0

- New: Dark-mode support for REDAXO >= 5.13 @schuer @skerbis
- Fixed: Version compare @skerbis

### Version 6.0.9 – 6.0.13

- Select fix. @dtpop, @IngoWinter, @skerbis, multiple issues fixed regarding single select- and multiselect-fields
- Allow callable @DanielWeitenauer
- new check for JSON Values 1.x.x


### Version 6.0.6

- fixed: delete all entries in imagelist @ynamite 
- fixed: wrong var prevents cusrtom classes on tabs @bitshiftersgmbh 

### Version 6.0.4

- Fixed missing external link in widget
- Some minor fixes
thx @lexplatt  @Hirbod

### Version 6.0.3
prepareCustomLink fixed


### Version 6.0.2
- readme style fixes @crydotsnake 
- remove .formcontrol on input fields type color @olien
- some validation methods changed and calls deleted @skerbis


### Version 6.0.1
- added some docs
- minor bugfixes


### Version 6.0.0

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

#### Breaking changes: 

The REX_CUSTOM_LINK Var now saves the data in a regular REX_VAR. So the usage of REX_CUSTOM_LINK is not backward compatible. You should move the values from Linklist to a value field. 

Parsley has been removed. AddValidation is functionless. 

removed `closeCollapse`, `closeTab`, `closeAccordion`
> Look at the new wrapper field examples


### Version 5.3

* added custom link as widget
* exchange custom link for yform and rex_form
* added image list widget for mfrom, rex_form and yform 

### Version 5.2.5

* add possibility to disable select options
* fix fieldset and grouping issues

### Version 5.2.4

* re-add special input types support

### Version 5.2.3

* removed tab history

### Version 5.2.2

* Parameter must be an array warning fix

### Version 5.2.1

* fixed: Media-Button Parameter type space
* Link-Title for Custom-Link Buttons added
* Attributes for media- and link-elements added, which allows validation via Parsley
* fixed: c14n html body wrapping removed

Changes: 

* now uses includeCurrentPageSubPath to show pages @christophboecker
* Cache buster will be added by rex core @staabm
* init.js simplified @staabm


### Version 5.2.0 pre-release

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

### Version 5.1.0

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
