# Select und Multiselect Elemente

> ## Inhalt
> - [Select-Typen als Formular Elemente](#Select-Typen)
> - [Select-Elemente](#Select)
> - [Multiselect-Element](#Multiselect)
> - [Optionen in Select- und Multiselect-Elementen](#Optionen)
> - [SQL-Optionen in Select- und Multiselect-Elementen](#SQL-Optionen)
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
* $options => `array(1=>'test-1',2=>'test-2')`
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
$mform->addSelectField(1, array(1=>'test-1',2=>'test-2'), array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add select field
$mform->addSelectField(1);
$mform->setOptions(array(1=>'test-1',2=>'test-2'));
$mform->setLabel('Label Name');
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
$mform->addMultiSelectField(1, array(1=>'test-1',2=>'test-2'), array('label'=>'Label Name'));
```

```
// instance mform
$mform = new MForm();

// add multi select field
$mform->addMultiSelectField(1);
$mform->setOptions(array(1=>'test-1',2=>'test-2'));
$mform->setLabel('Label Name');
```


<a name="Optionen"></a>
## Optionen in Select- und Multiselect-Elementen

> **Hinweis**
>
> * Damit ein Select oder Multiselect-Feld mit Inhalt erscheint sind Optionen zwingend erforderlich!


<a name="SQL-Optionen"></a>
##SQL-Optionen in Select- und Multiselect-Elementen

Es ist möglich direkt ein SQL Query über die Methode `setSqlOptions` abzusetzen. MForm kombiniert dann aus den Spalten der Select-Tabelle die entsprechenden Options.

*Einfaches Select-Element mit SQL-Optionen und Label*

```
// instance mform
$mform = new MForm();

// add select field
$mform->addSelectField(1);
$mform->setLabel('Label Name');

// add sql options
$mform->setSqlOptions('SELECT id, name FROM rex_table_name ORDER BY name');
```

*Einfaches Select-Element mit SQL-Optionen, Leer-Option und Label*

```
// instance mform
$mform = new MForm();

// add select field
$mform->addSelectField(1);
$mform->setLabel('Label Name');
// leeroption einsetzen
$mform->addOption('--- Leer ---', '');
// add sql options
$mform->setSqlOptions('SELECT id, name FROM rex_table_name ORDER BY name');
```

> **Wichtig**
>
> * Die Spaltennamen der Select-Tabelle müssen zwingend `id` für den Options-Wert und `name` für den Options-Name lauten.
> * Entspricht der Spaltenname der incrementelle Spalte der Select-Tabelle nicht dem Namen `id` kann man via `AS` das Select modifizieren `colum_id AS id`.
> * Selben Workaround sollte für die namensgebende Spalte `name` genutzt werden sollte dieser entsprechend anders benamt sein.
> * Options können beliebig kombiniert werden.


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
