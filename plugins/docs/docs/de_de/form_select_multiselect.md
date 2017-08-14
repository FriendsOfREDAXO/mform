# Select und Multiselect Elemente

> ## Inhalt
> - [Select-Typen als Formular Elemente](#Select-Typen)
> - [Select-Elemente](#Select)
> - [Multiselect-Element](#Multiselect)
> - [Optionen in Select- und Multiselect-Elementen](#Optionen)
> - [Weiterführende Links](#Links)

Die Gruppe Select und Multiselect Elemente umschließt alle Select Formular Elemente.


<a name="Select-Typen"></a>
## Select-Typen als Formular Elemente

Es gibt 2 Select-Typen die bereit gestellt werden, diese werden durch ihre jeweils eigene Methode aufgerufen:

* `addSelectField`
* `addMultiSelectField`

> **Wichtig**
>
> * Diese 2 Typen können als Formular Elemente eingesetzt werden. 
> * Die jeweiligen Typen-Methode nehmen als Parameter Options, Attribute, Default-Values entgegen.


*Exemplarische Übergabewerte, in den folgenden Beispiele nutzen wir diese Variablen:*

* $id => `1`
* $options => `array('1_name'=>'1_wert', '2_name'=>'2_wert')`
* $attributes => `array('label'=>'Label Name')`
* $size => `1` `full`
* $defaultValue => `1`  

> **Hinweis**
>
> * Der erste Übergabewerte `$id` ist immer obligatorisch.
> * Optionen sind zwingend erforderlich.
> * Die weiteren Übergabewerte sind optional.
> * Optionen und Attribute können nur als Arrays übergeben werden.
> * Der erste Wert `$id` muss der `REX_VALUE_ID` entsprechen.


<a name="Select"></a>
## Select-Elemente

*Erwartete Übergabewerte der “addSelectField” Methoden:*


`($id, $options, $attributes, $size, $validation, $defaultValue)`

*Einfaches Select-Element mit Optionen und Label*

```
// instance mform
$mform = new MForm();

// add select field
$mform->addSelectField(1, array(1=>'test-1',2=>'test-2'), array('label'=>'Select'));
```

```
// instance mform
$mform = new MForm();

// add select field
$mform->addSelectField(1.2);
$mform->setOptions(array(1=>'test-1',2=>'test-2'));
$mform->setLabel('Select 2');
```


<a name="Multiselect"></a>
## Multiselect-Element

*Erwartete Übergabewerte der “addMultiSelectField” Methoden:*

`($id, $options, $attributes, $size, $validation, $defaultValue)`

*Einfaches Multiselect-Element mit Optionen und Label*

```
// instance mform
$mform = new MForm();

// add multi select field
$mform->addMultiSelectField(1, array(1=>'test-1',2=>'test-2'), array('label'=>'Select'));
```

```
// instance mform
$mform = new MForm();

// add multi select field
$mform->addMultiSelectField(1);
$mform->setOptions(array(1=>'test-1',2=>'test-2'));
$mform->setLabel('Select 2');
```


<a name="Optionen"></a>
*Optionen in Select- und Multiselect-Elementen*

> **Hinweis**
>
> * Damit ein Select oder Multiselect-Feld mit Inhalt erscheint sind Optionen zwingend erforderlich!


<a name="Links"></a>
## Weiterführende Links

*Generell / Allgemein*

* [Elementzuweisungen](elements_general.md)
* [Elementen Attribute zuweisen](elements_attributes.md)
* [Elementen Optionen zuweisen](elements_options.md)

*Validierungen Select- und Multiselect-Elementen zuweisen*

* [Elementen Validierungen zuweisen](elements_validates.md)

*Default-Value Select- und Multiselect-Elementen zuweisen*

* [Default-Values definieren](elements_default_values.md)
