# Kurz-Doku und Referenz / Vorwort


MForm kann ähnlich eingesetzt werden wie die `rex_form` Klasse, dabei kann man mit verschiedenen Methoden einer Instanz Formular-Elemente hinzufügen und durch weitere Methoden den Elementen Attribute, Parameter, Optionen oder Validierungen zuweisen. Bei der Konstruktion von MForm wurden die Element-Methoden jedoch so angelegt, dass deren Konstruktoren jeweils alle möglichen Attribute, Parameter, Optionen oder Validierungen als Übergabe-Arrays verarbeiten, wodurch alle möglichen Elemente kurzgesagt "einzeilig" und mit wenig Aufwand angelegt werden können. Die unten angeführten Links führen zu "Modul-Input Demos" in welchen die Element-Methoden exemplarisch und weitestgehend mit allen möglichen Übergabe-Arrays versehen wurden.

Es können ohne weiteres beliebig viele Formulare in einem Modul-Input durch MForm erzeugt werden, hierzu muss lediglich jeweils eine neue Instanz initiiert werden.


###### MForm stellt folgende Formular Elemente bereit:


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
* System-Button-Elemente
  * `addLinkField`
  * `addLinklistField`
  * `addMediaField`
  * `addMedialistField`


###### Geplante Elemente

* Callback-Element
  * `callback`