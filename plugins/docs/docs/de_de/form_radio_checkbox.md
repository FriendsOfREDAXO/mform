# Radio und Checkbox Elemente

> ## Inhalt
> - [Radio und Checkboxen als Formular-Elemente](#Radio)
> - [Radiobutton-Elemente](#Radiobutton)
> - [Checkboxen-Element](#Checkboxen)
> - [Optionen in Radiobutton- und Checkbox-Elementen](#Optionen)
> - [Weiterführende Links](#Links)

Die Gruppe Radio- und Checkbox-Elemente stellt Checkbox-Formular-Elemente und Radio-Buttons zur Verfügung.


<a name="Radio"></a>
## Radio und Checkboxen als Formular-Elemente

Eine Radiobuttons-Gruppe oder Checkbox wird jeweils durch ihre eigene "Methode" aufgerufen es gibt 2 Typen:

* `addRadioField`
* `addCheckboxField`

> **Wichtig**
>
> * Diese 2 Typen können als Formular Elemente eingesetzt werden. 
> * Zu beachten ist, dass die Checkboxen Methode sich signifikant von der Radiobutton-Methode insofern unterscheidet, dass die Radiobutton-Methode alle im `$options`-Array enthaltenen Reihen-Elemente zu Radiobutton-Felder weiter verarbeitet wo hingegen die Checkbox Methode nur das letzte Reihen-Element zu einem Checkboxen-Feld verarbeitet. Will man mehrere Checkboxen anlegen muss entsprechend oft die Checkbox-Methode bemüht werden, was natürlich auch für Radiobutton-Gruppen gilt. 
> * Die jeweiligen Typen-Methode nehmen als Parameter Options, Attribute, Default-Values entgegen.


*Exemplarische Übergabewerte, in den folgenden Beispiele nutzen wir diese Variablen:*

* $id => `1`
* $options => `array(1=>'test-1',2=>'test-2')`
* $attributes => `array('label'=>'Label Name')`
* $validation => `array('empty')`
* $defaultValue => `1`  

> **Hinweis**
>
> * Der erste Übergabewerte `$id` ist immer obligatorisch.
> * Optionen sind zwingend erforderlich.
> * Die weiteren Übergabewerte sind optional.
> * Optionen und Attribute können nur als Arrays übergeben werden.
> * Der erste Wert `$id` muss der `REX_VALUE_ID` entsprechen.


<a name="Radiobutton"></a>
## Radiobutton-Elemente

*Erwartete Übergabewerte der `addRadioField` Methode:*

`($id, $options, $attributes, $validation, $defaultValue)`

*Beispiel einfachen Radiobutton mit Label und Optionen anlegen*

```
// instance mform
$mform = new MForm();

// add radio field
$mform->addRadioField(1, array(1=>'test-1',2=>'test-2'), array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add radio field
$mform->addRadioField(1);
$mform->setOptions(array(1=>'test-1',2=>'test-2'));
$mform->setLabel('Label Name');
```

<a name="Checkboxen"></a>
## Checkboxen-Element

*Erwartete Übergabewerte der `addCheckboxField` Methode:*

`($id, $options, $attributes, $validation, $defaultValue)`

*Beispiel einfachen Radiobutton mit Label und Optionen anlegen*

```
// instance mform
$mform = new MForm();

// add checkbox field
$mform->addCheckboxField(1, array(1=>'test-1'), array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add checkbox field
$mform->addCheckboxField(1);
$mform->setOptions(array(1=>'test-1'));
$mform->setLabel('Label Name');
```


<a name="Optionen"></a>
## Optionen in Radiobutton- und Checkbox-Elementen

> **Hinweis**
>
> * Damit ein Radiobutton- oder Checkbox-Elementen mit Wert erscheint sind Optionen zwingend erforderlich!


<a name="Links"></a>
## Weiterführende Links

*Generell / Allgemein*

* [Elementzuweisungen](elements_general.md)
* [Elementen Attribute zuweisen](elements_attributes.md)
* [Elementen Optionen zuweisen](elements_options.md)

*Validierungen Radiobutton- oder Checkbox-Elementen zuweisen*

* [Elementen Validierungen zuweisen](elements_validates.md)

*Default-Value Radiobutton- oder Checkbox-Elementen zuweisen*

* [Default-Values definieren](elements_default_values.md)
