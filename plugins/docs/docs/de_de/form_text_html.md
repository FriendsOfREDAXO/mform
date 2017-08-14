# Text und HTML Elemente


Mit der Gruppe Text- und HTML-Elemente wird die Möglichkeit bereitgestellt Beschreibungstexte und individuellen HTML-Code im Formular zu integrieren.


### Strukturelle und informative Formular Elemente


###### Die strukturellen und informativen Formular Elemente werden jeweils durch ihre eigene Methoden angesteuert:


* `addHtml`
* `addHeadline`
* `addDescription`


Die Konstruktoren der informativen Element-Methoden `addHtml`, `addHeadline`, `addDescription` erwartet jeweils ein Übergabewert. Der Konstruktor des strukturellen Formular Elements `addFieldset` erwartet 2 Übergabewerte.


###### Erwarteter Übergabewert der informativen Element-Methoden:


`($strValue)`


###### Erwarteter Übergabewert der strukturellen Element-Methoden:


`($strValue, $arrAttributes)`


* $strValue => `string`
* $arrAttributes => `array('label'=>'Label Name')`


##### Hinweis:


* Alle Übergabewerte sind optional.
* Attribute können nur als Arrays übergeben werden.
* Attribute stehen nur für strukturelle Formular Elemente zur Verfügung.


###### HTML Element


```php
  $objForm->addHtml('<b>HTML Code</b>');
```


###### Headline-Element


```php
  $objForm->addHeadline('Text Elemente');
```


###### Description-Element


```php
  $objForm->addDescription('Beschreibungstext auch Mehrzeilig');
```

###### Fieldset-Element


```php
  $objForm->addFieldset('Fieldset',array('class'=>'myfieldset'));
```


##### Hinweis: 


* Wird ein Fieldset angelegt ist dieses:
	- a) bis zum Ende des Formulars geöffnet und schließt nach dem letzten Formular-Element oder 
	- b) geöffnet bis ein anderes Fieldset-Element angelegt wird und umschließt folglich alle Elemente vom Fieldset-Element-Methode zu Fieldset-Element-Methode


***


### Attribute in strukturellen Elementen


##### Wiki-Links zum Thema:


* Grundlagen
  * [Elementzuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementzuweisungen)
  	  * [Elementen Attribute zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Attribute-zuweisen)
