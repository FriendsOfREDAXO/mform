# Elementen Attribute zuweisen

> ## Inhalt
> - [Methoden der Attribut-Uuweisung](#Attribute-zuweisen)
> - [Beispiele für Attribut-Übergaben](#Attribute-übergeben)
> - [Formular-Elemente die Attribute verarbeiten](#Formular-Elemente)
> - [Zulässigen und unzulässige Attribut Typen](#Attribut-Typen)

Mit der `setAttributes` Methode können beliebigen Formular-Elementen Attribute zugewiesen werden.


<a name="Attribute-zuweisen"></a>
## Methoden der Attribut-Zuweisung

Es gibt 3 Wege Elementen Attribute zuzuweisen:

1. In einem Übergabe-Array als Parameter der Element-Methode.
2. In einem Übergabe-Array als Parameter der `setAttributes`-Methode.
3. Als Name- und Wert-Parameter der `addAttribute`-Methode.

> **Hinweis**
> 
> * Das Übergabe-Array muss nach folgendem Schema Aufgebaut sein: `array('1_name'=>'1_wert', '2_name'=>'2_wert')`


<a name="Attribute-übergeben"></a>
## Beispiele für Attribut-Übergaben

*1. Beispiel für Zuweisung durch Element-Methode*

```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1, array('label' => 'Headline', 'style' => 'width:200px', 'class' => 'text_input_feld'));
```

*2. Beispiel für Zuweisung durch `setAttributes`-Methode*

```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1);

// add attribute 
$mform->setAttributes(array('label' => 'Headline', 'style' => 'width:200px', 'class' => 'text_input_feld'));
```

*3. Beispiel für Zuweisung durch `addAttribute`-Methode*

```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1);

// add attribute 
$mform->addAttribute('label', 'Headline');
$mform->addAttribute('style', 'width:200px');
$mform->addAttribute('class', 'text_input_feld');
```

> **Hinweis**
> 
> *Bei der Verwendung von Übergabe-Arrays*
> * Der Array-Key `label` übergibt gibt der `setLabel`-Methode seinen Wert als Parameter.
> * Der Array-Key `validation` ruft die `setValidiations`-Methode auf.
> * Der Array-Key `size` übergibt der `setSize`-Methode seinen Wert als Parameter.
> * Der Array-Key `default-value` übergibt der `setDefaultValue`-Methode seinen Wert als Parameter.


<a name="Formular-Elemente"></a>
## Formular-Elemente die Attribute verarbeiten

Attribute können folgenden Formular-Elementen zugewiesen werden:

* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addHiddenField`
  * `addTextAreaField`
  * `addTextReadOnlyField`
  * `addTextAreaReadOnlyField`
* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox- und Radio-Elemente
  * `addCheckboxField`
  * `addRadioField`
* Strukturelle-Elemente
  * `addHtml`
  * `addHeadline`
  * `addDescription`
  * `addFieldset`

> **Hinweis**
>
> * Auch wenn Attribute Elementen zugewiesen werden können welche in der Liste nicht aufgeführt wurden, werden diese jedoch dann nicht in das HTML dieser Elemente geschrieben.


<a name="Attribut-Typen"></a>
## Zulässigen und unzulässige Attribut Typen:

* Alle möglichen Attribute ausgenommen `id`, `name`, `type`, `value`, `checked` und `selected`.
* Das zulässige `validation` Attribut muss ein Array übergeben.
* Dem zulässigen `label` Attribut kann ein Array oder String übergeben werden.