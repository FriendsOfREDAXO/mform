# MForm - REDAXO Addon für Modul-Input-Formulare

### Version 5.2.0



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
