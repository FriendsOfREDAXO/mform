# Elementen Default-Values und/oder Placeholder zuweisen

> ## Inhalt
> - [Methoden der Zuweisungen](#Default-Value-zuweisen)
> - [Beispiele für Default-Value-Übergaben](#Default-Value)
> - [Beispiele für Placeholder-Zuweisungen](#Placeholder)
> - [Formular-Elemente welchen Default-Value und/oder Placeholder erhalten können](#Formular-Elemente)

Will man einem Formular-Element einen Default-Value vergeben kann man hierfür die `setDefaultValue` Methode nutzen. Um einen Placeholder zu setzen findet die `setPlaceholder`-Methode Verwendung. 

> **Hinweis**
>
> * Das setzen eines Default-Wertes schließt das einsetzen eines Placeholders nicht aus.
> * Der Placeholder würde in diesem Fall erscheinen, sobald der Editor den Default-Wert entfernt.


<a name="Default-Value-zuweisen"></a>
## Methoden der Zuweisungen

Es gibt je nach Element 3 Wege um ein Default-Value zu setzen:

1. Als Wert dem entsprechendem Parameter der Element-Methode.
2. Als Wert der `setDefaultValue`-Methode.
3. Als Wert durch die `setAttributes`-Methode. 

Je nach Element gibt es dann auch 2 Wege um einen Placeholder zu setzen:

1. Als Wert der `setPlaceholder`-Methode.
2. Als Wert durch die `setAttributes`-Methode. 

> **Hinweis**
>
> * Placeholder unterscheiden sich wesentlich von Default-Werten. 
> * Ein Placeholder wird bei einer Nichteingabe vom Editor nicht in das REX_VALUE geschrieben.
> * Ein Default-Wert wird hingegen übernommen und bei Nichteingabe des Editors gespeichert.


<a name="Default-Value"></a>
## Beispiele für Default-Value-Übergaben

*1. Beispiel für Zuweisung durch Element-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(2,array('label' => 'Label Name'), array(), 'Default Value');
```

*2. Beispiel für Zuweisung durch `setDefaultValue`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(2, array('label' => 'Label Name'));

// set default value
$mform->setDefaultValue('Default Value');
```

*3. Beispiel für Zuweisung durch `setAttributes`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(2, array('label' => 'Label Name'));

// set attributes
$mform->setAttributes('default-value' => 'Default Value');
```


<a name="Placeholder"></a>
## Beispiele für Placeholder-Zuweisungen

*1. Beispiel für Zuweisung durch `setPlaceholder`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(2, array('label' => 'Label Name'));

// set default value
$mform->setPlaceholder('Placeholder');
```

*2. Beispiel für Zuweisung durch `setAttributes`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(2, array('label' => 'Label Name'));

// set attributes
$mform->setAttributes('placeholder' => 'Placeholder');
```

<a name="Formular-Elemente"></a>
## Formular-Elemente welchen Default-Value und/oder Placeholder erhalten können

Default-Values und/oder Placeholder können für folgende Formular-Elemente gesetzt werden:

* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addTextAreaField`

Die weiteren Elemente können nur Default-Values jedoch nicht mit Placeholdern versehen werden:

* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`


> **Hinweis**
>
> * Auch wenn Default-Values und Placeholder Elementen zugewiesen werden können, welche in der Liste nicht aufgeführt wurden, werden diese jedoch dann nicht im HTML dieser Elemente geschrieben.
> * Default-Values von Select-, Radio- und Checkbox-Elemente dürfen nur deren "Key-Werten" entsprechen.