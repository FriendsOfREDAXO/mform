# Elementen Validierungen zuweisen

> ## Inhalt
> - [Methoden der Validierungs-Zuweisung](#Validierung-zuweisen)
> - [Beispiele für Validierungs-Übergaben](#Validierung-übergeben)
> - [Formular-Elemente welche validiert werden können](#Formular-Elemente)
> - [Zulässige Validierungen](#Zulässige-Validierungen)

Mit MForm ist es möglich ohne Actions Input-Formulare mit Validierungen zu versehen. Dabei greift MForm auf ein geläufiges jQuery Plugin [Parsley](http://parsleyjs.org/documentation.html) zurück. Die Validierungen lassen sich je nach Element als Attribut oder separiertes Übergabe-Array definieren.

Mit der Methode `setValidations` können Formular-Elementen Validierungen zugewiesen werden.


<a name="Validierung-zuweisen"></a>
## Methoden der Validierungs-Zuweisung

Es gibt 4 Wege eine Validierung zuzuweisen:

1. In einem Übergabe-Array als Parameter der Element-Methode.
2. In einem Übergabe-Array als Parameter der `setValidations`-Methode.
3. Als Name- und Wert-Parameter der `addValidation`-Methode.
4. Als Wert über die `setAttributes`-Methode.

> **Hinweis**
>
> * Das Übergabe-Array muss nach folgendem Schema aufgebaut sein: `array('Validierungsbezeichnung_1', 'Validierungsbezeichnung_2')`


<a name="Validierung-übergeben"></a>
## Beispiele für Validierungs-Übergaben

**Beispiel für Aufruf von Email- und Empty-Validierungen**

*1. Beispiel für Zuweisung durch Element-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(1, array('label' => 'E-Mail'), array('compare', 'empty'));
```

*2. Beispiel für Zuweisung durch `setValidations`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(1, array('label' => 'E-Mail'));

// add validation
$mform->setValidations(array('compare', 'empty'));
```

*3. Beispiel für Zuweisung durch `addValidation`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(1, array('label' => 'E-Mail'));

// add validation
$mform->addValidation('compare');
$mform->addValidation('empty');
```

*4. Beispiel für Zuweisung durch `setAttributes`-Methode*

```php
// instance mform
$mform = new MForm();

// add text field
$mform->addTextField(1);

// add attributes
$mform->setAttributes(array('label'=>'E-Mail','validation'=>array('compare','empty')));
```

> **Hinweis** 
>
> * Validierungen werden als `data-attribut` in das entsprechenden Element geschrieben.


<a name="Formular-Elemente"></a>
## Formular-Elemente welche validiert werden können 

Validierungen können Folenden Formular-Elementen zugewiesen werden:

* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addTextAreaField`
* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox-Elemente
  * `addCheckboxField`

> **Hinweis**
>
> * Nicht alle Formular Elemente reagieren auf eingesetzt Validierungen.
> * Validierungen lassen sich ggf. kombinieren.


<a name="Zulässige-Validierungen"></a>
## Zulässige Validierungen

Folgende Validierungen sind zulässig:

* `empty`
* `integer`
* `float`
* `alphanum`
* `dateIso`
* `compare` or `email`
* `minlength` *
* `maxlength` *
* `min` *
* `max` *
* `url`
* `regexp` *
* `data-mincheck` *
* `data-maxcheck` *

> **Hinweis**
>
> * Die mit * gekennzeichneten Validierungen benötigen einen Verarbeitungswert.
> * Die Validierungsbezeichnung wird hier als Array-Key genutzt. 
> * Im Array-Value wird dann der Verarbeitungswert erfasst: `array('Validierungsbezeichnung'=>'Verarbeitungswert')`