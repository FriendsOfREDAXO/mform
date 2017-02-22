# Elementen Optionen zuweisen

> ## Inhalt
> - [Methoden der Options-Zuweisung](#Optionen-zuweisen)
> - [Beispiele für Optionen-Übergaben](#Optionen-übergeben)
> - [Formular-Elemente die Optionen verarbeiten](#Formular-Elemente)

Mit der Methode `addOptions` können Formular-Elemente diverse Optionen zugewiesen werden.


<a name="Optionen-zuweisen"></a>
## Methoden der Options-Zuweisung

Es gibt 3 Wege Select-, Radio- und Checkbox-Elementen Optionen zuzuweisen:

1. In einem Übergabe-Array als Parameter der Element-Methode.
2. In einem Übergabe-Array als Parameter der `setOptions`-Methode.
3. Als Name- und Wert-Parameter der `addOption`-Methode.

> **Hinweis**
>
> * Die Übergabe-Arrays müssen nach folgendem Schema Aufgebaut sein: `array('1_name'=>'1_wert', '2_name'=>'2_wert')`
> * Optionen sind für Select-, Radio- und Checkbox-Elementen erforderlich. 


<a name="Optionen-übergeben"></a>
## Beispiele für Optionen-Übergaben

*1. Beispiel für Zuweisung durch Element-Methode*

```php
// instance mform
$mform = new MForm();

// add select field
$mform->addSelectField(2, array(1 => 'test-1', 2 => 'test-2'));
```

*2. Beispiel für Zuweisung durch `setOptions`-Methode*

```php
// instance mform
$mform = new MForm();

// add link button field
$mform->addSelectField(1);

// set parmaeter
$mform->setOptions(array(1 => 'test-1', 2 => 'test-2'));
```

*3. Beispiel für Zuweisung durch `addOption`-Methode*

```php
// instance mform
$mform = new MForm();

// add link button field
$mform->addSelectField(1);

// set parmaeter
$mform->addOption(1, 'test-1');
$mform->addOption(2, 'test-2');
```

> **Hinweis**
>
> * Die `setOptions`-Methode verarbeitet keine Fremdwerte wie beispielsweise die `setAttributes` Methode.


<a name="Formular-Elemente"></a>
## Formular-Elemente die Optionen verarbeiten

Folgende Elemente benötigen zwingend Optionen:

* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`

> **Hinweis**
>
> * Auch wenn Optionen anderen Elementen zugewiesen werden können haben diese jedoch ausschließlich für Select-, Radio- und Checkbox-Elemente eine Bedeutung. 
> * Select-, Radio- und Checkbox-Elemente benötigen Optionen zwingend, werden keine zugewiesen, wird das Formular-Element nicht erzeugt.