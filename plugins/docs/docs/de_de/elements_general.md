# Elementzuweisungen

> ## Inhalt
> - [Elementen Attribute, Parameter, Optionen und Validierungen zuweisen](#Elemente-zuweisen)
> - [Zuweisung durch den Einsatz von `set`-Methoden](#set-Methoden)
> - [Zuweisung durch den Einsatz von `add`-Methoden](#add-Methoden)
> - [Zuweisung durch Übergabe an die Element-Methoden](#Element-Methoden)
> - [Zuweisung durch speziale `setter`-Methoden](#setter-Methoden)
> - [Mögliche Zuweisungen - Detailierte Beschreibungen](#Links)


Formular-Elemente können Attribute, Parameter, Optionen oder Validierungen zugewiesen bekommen. Dabei ist es vom Element abhängig ob diese möglich oder nötig sind. Text-Input- oder Hidden-Elemente können beispielsweise mit Attributen versehen werden. Select-Elemente hingegen müssen Optionen erhalten.


<a name="Elemente-zuweisen"></a>
## Elementen Attribute, Parameter, Optionen und Validierungen zuweisen

Generell sind die Element-Methoden so angelegt, dass Attribute, Parameter, Optionen oder Validierungen als Arrays an die jeweiligen Parameter der Element-Methoden übergeben werden können, zudem ist es aber auch möglich diese durch Aufruf ihrer eigenen Methoden hinzuzufügen.


> **Generelle Regel**
>
> *Hierfür gilt immer die generelle Regel:*
> * **Vom erzeugten Element bis zum nächsten werden alle durch ihre Methode eingesetzten Attribute, Parameter, Optionen und Validierungen dem erzeugten Element zugewiesen.**
> 
> *Und:* 
> * **Elemente für welche Attribute oder Parameter oder Optionen oder Validierungen nicht zulässig sind verarbeiten diese nicht.**
> 
> *Zudem:*
> * **Die zuletzt übergebenen Zuweisungen überschreiben eventuell vorher eingesetzt Zuweisungen.**


<a name="set-Methoden"></a>
## Zuweisung durch den Einsatz von `set`-Methoden

Die Methoden `setAttributes`, `setParameter`, `setValidations` und `setOptions` können dazu genutzt werden um einem Element entsprechend Attribute, Parameter, Validationen oder Optionen hinzuzufügen. 
Die Methoden nehmen jeweils einen Parameter an, dieser erwartet ein Array in welchem die Zuweisungen durch Name und Wert übergeben werden.

**Dieses Array ist wie folgt aufgebaut:**

`array('1_name'=>'1_wert', '2_name'=>'2_wert')`

*Beispiel:*
 
```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1);

// add attribute 
$mform->setAttributes(array('label' => 'Headline'));
```

> **Hinweis**
>
> * In dem Übergabe-Array können beliebig viele Zuweisungen erfasst werden. Doch nur zulässige Werte werden verarbeitet.


<a name="add-Methoden"></a>
## Zuweisung durch den Einsatz von `add`-Methoden

Die Methoden `addAttribute`, `addParameter`, `addValidation` und `addOption` können dazu genutzt werden um einem Element entsprechend Attribute, Parameter, Validationen oder Optionen einzeln hinzuzufügen.
Die Methoden nehmen zwei Parameter an, der erste Parameter erwartet den Namen der zweite den Wert der Zuweisung.


*Beispiel:*
 
```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1);

// add attribute 
$mform->addAttribute('label', 'Headline');
```

<a name="Element-Methoden"></a>
## Zuweisung durch Übergabe an die Element-Methoden

Zuweisungen können auch direkt durch übergabe der Übergabe-Arrays als Parameter in die Element-Methoden getroffen werden. Es ist abhängig von der Element-Methode welche Übergabe-Arrays diese entgegen nehmen.

Die Interfaces für die unterschiedlichen Element-Methoden werden in den unten aufgeführten Doku-Seiten umfangreich beleuchtet.

*Interface TextField-Methode*

```php
addTextField($id, $attributes = array(), $validations = array(), $defaultValue = NULL)
```

Die `addTextField`-Methode nimmt, wie man im, als Beispeil angebrachten Interface, sehen kann, mehrere Parameter entgegen. Die Methode erwartet entsprechend Übergabe-Arrays für `$attributes` und `$validations`.

*Beispiel:*
 
```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1, array('label' => 'Headline'));
```

> **Hinweis**
>
> * Die Übergabe-Arrays müssen nicht befüllt werden sie können optional an die entsprechenden Parameter geliefert werden.
> * Die Element-Methoden haben unterschieldiche Interfaces abhängig davon ob sie Attribute, Optionen, Validierungen, etc. verarbeiten können.


<a name="setter-Methoden"></a>
## Zuweisung durch speziale `setter`-Methoden

Es gibt Standard-Eigenschaften von Elements welche generel durch speziell bereitgestellte `setter`-Methoden einem Element zugewiesen werden können.

* Spezielle `setter`-Methoden
  * `setLabel`
  * `setPlaceholder`
  * `setMultiple`
  * `setSize`


<a name="Links"></a>
## Mögliche Zuweisungen - Detailierte Beschreibungen

- [Elementen Attribute zuweisen](elements_attributes.md)
- [Elementen Parameter zuweisen](elements_params.md)
- [Elementen Optionen zuweisen](elements_options.md)
- [Elementen Validierungen zuweisen](elements_validates.md)
- [Elemente mit Default-Value versehen](elements_default_values.md)
- [Sonstige Elementzuweisungen](elements_others.md)
