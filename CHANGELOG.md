# MForm - REDAXO Addon für Modul-Input-Formulare

### Version 5.3.2
* Docs for custom link rex_form @dpf-dd
* Fixed: https://github.com/FriendsOfREDAXO/mform/issues/171
* Description Style fix @IngoWinter


### Version 5.3.1
* fixed: JSON-Values could not be used in 5.3

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
