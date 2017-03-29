# Einstieg / Erste Schritte

> ## Inhalt
> - [MForm - HTML Formulare für Module](#MForm-HTML)
> - [Das erste MForm Formular anlegen und verstehen](#Das-Erste-MForm-Formular)
> - [Short Schreibweise](#Short-Schreibweise)
> - [Unterschiedliche Formular Elemente](#Formular-Elemente)
> - [Element-Methoden](#Element-Methoden)

<a name="MForm-HTML"></a>
## MForm - HTML Formulare für Module

Redaxo Module sind so angelegt, dass im einfachsten Fall a) der Modul-Input als HTML-Formular erfasst wird und b) der Modul-Output ebenfalls als HTML-Segment deklariert wird. Die Eingaben in den Modul-Input-Formularen werden von Redaxo in den `REX_VALUES` gespeichert. In einem Modul-Input-Formular kann einfach durch Setzen eines `name`-Attribut festgelegt werden in welchen `REX_INPUT_VALUE` die Eingabe des Input-Feldes gespeichert werden soll. Im Modul-Output kann diese Eingabe dann durch das Aufrufen des entsprechenden `REX_VALUE` ausgegeben werden.

Mit MForm ist es möglich Modul-Input-Formulare zu definieren und diese als HTML parsen zu lassen. Wesentlich mehr erledigt MForm auch nicht. MForm erzeugt lediglich Modul-Input-Formulare und befüllt diese mit den `REX_VALUES`, `REX_MEDIA` und `REX_LINK` Werten die beim Anlegen eines Blockes in das Formular eingegeben wurden. 

> **Hinweis**
>
> * MForm ist weder am Prozess der Datenerfassung noch am Prozess der Datenlieferung beteiligt.
> * Durch MForm können Modul-Input-Formulare definiert und im Modul-Input geprintet werden. 
> * MForm wurde grundlegend und ausschließlich zum Erstellen von Modul-Input-Fomrularen konstruiert. 


<a name="Das-Erste-MForm-Formular"></a>
## Das erste MForm Formular anlegen und verstehen

MForm kann ähnlich eingesetzt werden wie die `rex_form` Klasse, dabei kann man mit verschiedenen Methoden einer Instanz Formular-Elemente hinzufügen und durch weitere Methoden den Elementen Attribute, Parameter, Optionen oder Validierungen zuweisen.
 
1. MForm Objekt instanziert: `$mform = new MForm();`
2. Fieldset anlegen: `$mform->addFieldset("Legend Text");`
3. Formular Element anlegen: `$mform->addTextField(1);`
4. Dem Text-Input-Element ein Label geben: `$mform->addLabel("Headline");`
5. Das Formular parsen und printen: `echo $mform->show();`


> **Hinweise**
>
> * Das Fieldsete wird gebraucht um Bootstrap konformen HTML Code zu genrieren.
> * Dem `TextField` wurde der Integere Wert `1` als Parameter mit geliefert. Dieser Wert verweist auf den `REX_INPUT_VALUE[1]` siehe untern *Geparstes Modul-Input-Formular HTML* `name="REX_INPUT_VALUE[1]"`
> * `echo` oder `print` wird benötigt um den geparsten HTML Code auch anzeigen zu lassen die Methode `show` returnt den HTML Code als String.

*Modul-Input:*

```php
<?php
// instance mform
$mform = new MForm();

// set fieldset
$mform->addFieldset("Legend Text");

// add textinput field
$mform->addTextField(1);

// set label for textinput field
$mform->setLabel("Headline");

// show the form
echo $mform->show();
?>
```

*Geparstes Modul-Input-Formular HTML:*

```html
<div class="mform">
    <fieldset class="form-horizontal "><legend>Legend Text</legend>
    <div class="form-group">
        <div class="col-sm-2 control-label"><label for="rv2_1">Headline</label></div>
        <div class="col-sm-10"><input id="rv2_1" type="text" name="REX_INPUT_VALUE[1]" value="" class="form-control "></div>
    </div>
    </fieldset>
</div>
```

<a name="Short-Schreibweise"></a>
## Short Schreibweise

Bei der Konstruktion von MForm wurden die Element-Methoden so angelegt, dass alle möglichen Attribute, Parameter, Optionen oder Validierungen als Übergabe-Parameter optional erwartet werden. Somit können alle möglichen Elemente kurzgesagt "einzeilig" und mit wenig Aufwand angelegt werden. 

*Modul-Input:*

```php
<?php
// instance mform
$mform = new MForm();

// set fieldset
$mform->addFieldset("Legend Text");

// add textinput field
$mform->addTextField(1, array('lable' => 'Headline'));

// show the form
echo $mform->show();
?>
```

Im Modul-Input Beispiel wurde dem Text-Input Feld `addTextField` ein Attribut-Array übergeben, über welches das Label definiert wurde. Dadurch ist der direkte Aufruf der `setLabel` Methode nicht mehr nötig.

> **Hinweis**
>
> * Alle Element-Methoden nehmen unterschiedliche Parameter entgegen, wodurch die "Short Schreibweise" überhaupt erst möglich wird. 
> * Mit dem nachträgliche Aufrufen von Settern werden, als Parameter übergebene Attribute, überschrieben.  


<a name="Formular-Elemente"></a>
## Unterschiedliche Formular Elemente

MForm stellt alle relevanten Formular-Elemente bereit. Zudem nutzt MForm die Redaxo Widgets `REX_LINK`, `REX_LINKLIST`, `REX_MEDIA` und `REX_MEDIALIST` wodurch die System-Eingabe-Elemente einfach in einem MForm Modul platziert werden können.

*Formular Elemente:*

* Text-Input
* Textarea
* Hidden Text-Input
* Hidden Textarea
* Select
* Multiple Select
* SQL Select
* Checkboxen
* Radioboxen
* Fieldsets
* Bootstrap-Tabs
* Rex-Link-List
* Rex-Link
* Rex-Media-List
* Rex-Media
* Custom-Link


> **Hinweis**
>
> * Es können beliebig viele Formular-Input-Elemente definiert werden.
> * Das Schließen von Fieldsets ist nicht nötig.
> * Tabs können wie Fieldsets eingesetzt werden.
> * Es können, ohne weiteres, beliebig viele Formulare in einem Modul-Input durch MForm erzeugt werden, hierzu muss lediglich jeweils eine neue Instanz initiiert werden.

<a name="Element-Methoden"></a>
## Element-Methoden 

MForm stellt folgende Element-Methoden bereit:

* Fieldset
  * `addFieldset`
  * `closeFieldset`
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
  * `addTab`
* System-Button-Elemente
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`
* Custom-Elemente
  * `addCustomLinkField`
  * `addInputField`
* Spezielle `setter`-Methoden
  * `setLabel`
  * `setPlaceholder`
  * `setMultiple`
  * `setSize`


__Geplante Elemente__

* Callback-Element
  * `callback`
* Struktur-Elemente
  * `columns`