# Elementen Validierungen zuweisen


Mit MForm ist es möglich ohne Actions Input-Formulare mit Validierungen zu versehen. Dabei greift MForm auf ein geläufiges jQuery Plugin [Parsley](http://parsleyjs.org/documentation.html) zurück. Die Validierungen lassen sich je nach Element als Attribut oder separiertes Übergabe-Array definieren.

Mit der Methode `setValidations` können Formular-Elementen Validierungen zugewiesen werden. Validierungen können dem Konstruktor der Element-Methoden über das "Attribut-Array" übergeben werden.


##### Es gibt also je nach Element bis zu 3 Wege eine Validierung zu definieren:


* Als Wert über den Konstruktor der Element-Methode.
* Als Wert über den Konstruktor der `setValidations` Methode.
* Als Wert über die `setAttributes` Methode.


##### Hinweis:


* Validierungen werden als Array dem Konstruktor übergeben.
* Das Übergabe-Array muss nach folgendem Schema aufgebaut sein: `array('Validierungsbezeichnung_1', 'Validierungsbezeichnung_2')`
* Das Übergabe-Array von Validierungen welche ein Übergabewert erwarten muss nach folgendem Schema aufgebaut sein: `array('Validierungsbezeichnung'=>'Verarbeitungswert')`

###### Beispiel für Aufruf von Email- und Empty-Validierungen:

```php
  $objForm->addTextField(1);
  $objForm->setLabel('Label Name');
  $objForm->setAttributes(array('style'=>'width:200px'));
  $objForm->setValidations(array('compare','empty'));

  $objForm->addTextField(2);
  $objForm->setAttributes(array('label'=>'Label Name','validation'=>array('compare','empty')));

  $objForm->addTextField(3,array('label'=>'Label Name','style'=>'width:200px','validation'=>array('compare','empty')));
```


##### Hinweis: 


* Validierungen werden als `data-attribut` in das entsprechenden Element geschrieben.


###### Validierungen können Folenden Formular-Elementen zugewiesen werden:

* Text-Input- und Hidden-Elemente
  * `addTextField`
  * `addTextAreaField`
* Select-Elemente
  * `addSelectField`
  * `addMultiSelectField`
* Checkbox-Elemente
  * `addCheckboxField`


##### Hinweis:


* Nicht alle Formular Elemente reagieren auf eingesetzt Validierungen.
* Validierungen lassen sich ggf. kombinieren.


###### Folgende Validierungen sind zulässig:


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


Die mit * gekennzeichneten Validierungen benötigen einen Verarbeitungswert. Die Validierungsbezeichnung wird hier als Array-Key genutzt. Im Array-Value wird dann der Verarbeitungswert erfasst:

`array('Validierungsbezeichnung'=>'Verarbeitungswert')`