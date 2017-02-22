# MForm Formulare erzeugen

> ## Inhalt
> - [MForm Objekt-Initialisierung](#Objekt-Initialisierung)
> - [MForm Element-Erzeugung](#Element-Erzeugung)
> - [MForm Formular-Parsing und Anzeige](#Formular-Parsing)
> - [Beispiel Modul-Input](#Modul-Input)

MForm-Formulare werden durch Verwendung der `MForm` Klasse erzeugt. Nach der Instanzierung eines Formular-Objekts können, durch Aufruf der jeweiligen Element-Methoden, Formular-Elemente angelegt werden. Letztlich wird dann das Formular durch Aufruf der `show` Methode geparst.


<a name="Objekt-Initialisierung"></a>
## MForm Objekt-Initialisierung

Ähnlich wie z.B. `rex_form` muss auch für MForm ein Objekt instanziert werden.

```php
// instance mform
$mform = new MForm();
```


<a name="Element-Erzeugung"></a>
## MForm Element-Erzeugung

Nach dem das Formular-Objekt initialisiert wurde können durch Aufruf der jeweiligen Element-Methoden Elemente erzeugt werden.

```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField("1.0", array('label'=>'Label Name','style'=>'width:200px'));
```

> **Hinweis**
>
> * Ab Version 2.2.0 benötigt MForm keine `REX_VALUE[x]`-Übergabe mehr.
> * Da MForm ab der Version 2.2.0 die ab Redaxo 4.5 möglichen `REX_VALUE-ARRAYs` unterstützt gibt es praktisch keine `REX_VALUE`-Limitierung mehr. 
> * Wer ein `REX_VALUE` als Array nutzen möchte muss an die `REX_VALUE_ID` den Array-Key Punkt-getrennt anhängen.
>
> **Wichtig**
>
> * Integere Zahlenwerte > 0 müssen nicht zwingend mit Anführungszeichen umschlossen werden. 
> * Findet sich als letzter Zahlenwert 0 im Punkt-getrennten Array-Key für das JSON-Array an letzter Stelle muss der gesamte Array-Key mit Anführungszeichen umschlossen werden. 
> * Auch alle nicht integeren Werte müssen umschlossen werden.


<a name="Formular-Parsing"></a>
## MForm Formular-Parsing und Anzeige

Wurden alle Elemente definiert muss final das Formular geparst und angezeigt werden. Die Methode `show` gibt den geparsten Formular HTML Code zurück.  


```php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField("1.0", array('label'=>'Label Name','style'=>'width:200px'));

// show the form
echo $mform->show();
```

> **Hinweis**
> 
> * Da die `show`-Methode lediglich den geparsten HTML Code als String zurück liefert muss ihr ein `echo` oder `print` vorangestellt werden.


<a name="Modul-Input"></a>
## Beispiel Modul-Input

*MForm Modul-Input-Code:*

```php
<?php
// instance mform
$mform = new MForm();

// add textinput field
$mform->addTextField(1.0,array('label'=>'Label Name','style'=>'width:200px'));

// show the form
echo $mform->show();
?>
```

*Generiertes Modul-Input-Formular aus obigem Code-Beispiel:*

```html
<div class="mform">
  <div class="form-group">
    <div class="col-sm-2 control-label"><label for="rv1_1_0">Label Name</label></div>
    <div class="col-sm-10"><input id="rv1_1_0" type="text" name="REX_INPUT_VALUE[1][0]" value="" class="form-control " style="width:200px"></div>
  </div>
</div>
```

> **Hinweis**
>
> * Dieses Beispiel nutzt das MForm-"Default"-Thema. Es ist auch möglich eigene Themes anzulegen.
> * In dem Beispiel wurde kein Fieldset eingesetzt, dies führt dazu, dass die Darstellung nicht optimal ist. 
> * Unter Verwendung des Default-Themas sollte man immer nach der Initialisierung der MForm Klasse ein Fieldset anlegen. 