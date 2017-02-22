# Elementen Parameter zuweisen

> ## Inhalt
> - [Methoden der Parameter-Zuweisung](#Parameter-zuweisen)
> - [Beispiele für Parameter-Übergaben](#Parameter-übergeben)
> - [System-Button-Elemente die Parameter verarbeiten](#System-Button-Elemente)
> - [Zulässige Parameter Typen](#Parameter-Typen)

Mit der Methode `setParameters` können System-Button-Elementen diverse Parameter zugewiesen werden.

   
<a name="Parameter-zuweisen"></a>
## Methoden der Parameter-Zuweisung

Es gibt 3 Wege System-Button-Elementen Parameter zuzuweisen:

1. In einem Übergabe-Array als Parameter der Element-Methode.
2. In einem Übergabe-Array als Parameter der `setParameters`-Methode.
3. Als Name- und Wert-Parameter der `addParameter`-Methode. 

> **Hinweis**
> 
> * Das Übergabe-Array muss nach folgendem Schema Aufgebaut sein: `array('1_name'=>'1_wert', '2_name'=>'2_wert')`


<a name="Parameter-übergeben"></a>
## Beispiele für Parameter-Übergaben

*1. Beispiel für Zuweisung durch Element-Methode*

```php
// instance mform
$mform = new MForm();

// add link button field
$mform->addLinkField(1,array('category'=>1,'label'=>'Interner Link'));
```

*2. Beispiel für Zuweisung durch `setParameters`-Methode*

```php
// instance mform
$mform = new MForm();

// add link button field
$mform->addLinkField(1);

// set parmaeter
$mform->setParameters(array('category'=>1,'label'=>'Interner Link'));
```

*3. Beispiel für Zuweisung durch `addParameter`-Methode*

```php
// instance mform
$mform = new MForm();

// add link button field
$mform->addLinkField(1);

// set parameter
$mform->addParameter('label', 'Interner Link);
$mform->addParameter('category', 1);
```

> **Hinweis**
>
> * Der Array-Key `label` übergibt gibt der `setLabel`-Methode seinen Wert als Parameter.


<a name="System-Button-Elemente"></a>
## System-Button-Elemente die Parameter verarbeiten

Folgende System-Button-Elemente reagieren auf Parameter:

* System-Button-Elemente
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`

> **Hinweis**
>
> * Auch wenn Parameter für alle anderen Elemente zugewiesen werden können haben diese jedoch ausschließlich für System-Button-Elemente eine Bedeutung.


<a name="Parameter-Typen"></a>
## Zulässige Parameter Typen

Zulässige Parameter für Link-, und Linklisten-Buttons:

* `category`, `label`
* Dem zulässigen `label` Attribut kann ein Array oder String übergeben werden.

*Zulässige Parameter für Media-, und Medialiste-Buttons:*

* `types`, `preview`, `category`, `label`
* Dem zulässigen `label` Attribut kann ein Array oder String übergeben werden.