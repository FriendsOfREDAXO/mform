# Textareas, Text-Input- und Hidden-Elemente

> ## Inhalt
> - [Input-Typen als Formular-Element](#Typen)
> - [Text-Input Elemente](#Text-Input)
> - [Readonly-Text-Input Elemente](#Text-Input-Readonly)
> - [Textarea Elemente](#Textarea)
> - [Readonly-Textarea Elemente](#Textarea-Readonly)
> - [Hidden-Text-Input Elemente](#Text-Input-Hidden)
> - [Weiterführende Links](#Links)

Die Gruppe Textarea, Text-Input- und Hidden-Elemente umschließt alle Texteingabe-Formular-Elemente durch welche Text-Strings erfasst werden können.


<a name="Typen"></a>
## Input-Typen als Formular-Element

Unterschiedliche Input-Typen werden durch jeweils ihre eigene "Methoden" angesteuert, es gibt 5 Typen:

* `addTextField`
* `addHiddenField`
* `addTextAreaField`
* `addTextReadOnlyField`
* `addTextAreaReadOnlyField`

> **Wichtig**
>
> * Diese 5 Typen können als Formular-Input-Elemente eingesetzt werden. 
> * Die jeweiligen Typen-Methode nehmen als Parameter, Attribute, Variablen, Default-Values, Validierungen entgegen.


*Exemplarische Übergabewerte, in den folgenden Beispiele nutzen wir diese Variablen:*

* $id => `1`
* $attributes => `array('label'=>'Label Name')`
* $value => `Value Text`
* $validations => `array('empty')`
* $defaultValue => `'Default-Value'`

> **Hinweis**
>
> * Der erste Übergabewert `$id` ist immer obligatorisch.
> * Die weiteren Übergabewerte sind optional.
> * Attribute können nur als Array übergeben werden.
> * Der erste Wert `$id` muss der `REX_INPUT_VALUE_ID` entsprechen.


<a name="Text-Input"></a>
## Text-Input Elemente

*Erwartete Übergabewerte der `addTextField` Methode:*

`($id, $attributes, $validations, $defaultValue)`

*Beispiel einfaches Textfeld anlegen*
 
```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1);
```

*Beispiel einfaches Textfeld mit Attributen, Validierung und Default-Value anlegen*

```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1, array('label'=>'Label Name'), array('empty'), 'Default-Value');
```


<a name="Text-Input-Readonly"></a>
## Readonly-Text-Input Elemente

*Erwartete Übergabewerte der `addTextReadOnlyField` Methode:*

`($id, $value, $attributes)`

*Beispiel einfaches readonly Textfeld anlegen*
 
```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextReadOnlyField(1, 'Value Text', array('label'=>'Label Name');
```


<a name="Textarea"></a>
## Textarea Elemente

*Erwartete Übergabewerte der `addTextAreaField` Methode:*

`($id, $attributes, $validations, $defaultValue)`

*Beispiel einfache Textarea anlegen*
 
```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextAreaField(1);
```

*Beispiel einfache Textarea mit Attributen, Validierung und Default-Value anlegen*

```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextAreaField(1, array('label'=>'Label Name'), array('empty'), 'Default-Value');
```


<a name="Textarea-Readonly"></a>
## Readonly-Textarea Elemente

*Erwartete Übergabewerte der `addTextAreaReadOnlyField` Methode:*

`($id, $value, $attributes)`

*Beispiel einfache readonly Textarea anlegen*

```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextAreaReadOnlyField(1, 'Value Text', array('label'=>'Label Name'));
```


<a name="Text-Input-Hidden"></a>
## Hidden-Text-Input Elemente

*Erwartete Übergabewerte der `addHiddenField` Methode:*

`($id, $value, $attributes)`

*Beispiel einfache Input-Hidden-Field anlegen*

```
// instance mform
$mform = new MForm();

// add textinput field
$mform->addHiddenField(1, 'Value Text', array('data-hidden'=>'hiddenfield');
```


<a name="Links"></a>
## Weiterführende Links

*Generell / Allgemein*

* [Elementzuweisungen](elements_general.md)
* [Elementen Attribute zuweisen](elements_attributes.md)

*Validierungen Text-Input-Elementen zuweisen*

* [Elementen Validierungen zuweisen](elements_validates.md)

*Default-Value Text-Input-Elementen zuweisen*

* [Default-Values definieren](elements_default_values.md)
