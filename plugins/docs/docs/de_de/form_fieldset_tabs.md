# Tabs- und Fieldset-Elemente

> ## Inhalt
> - [Strukturelle Formular Elemente](#Strukturelle-Elemente)
> - [Fieldset-Element](#Radiobutton)
> - [Tab-Element](#Checkboxen)
> - [Weiterführende Links](#Links)

Durch die Tabs- und Fieldset-Elemente wird die Möglichkeit geboten Formulare klar sturkturieren zu können. In dem Redaxo Bootstrap-default Modul-Formular-Layout und somit auch im MForm Default-Themas sollte man generell Fieldsets einsetzen. 
 

<a name="Strukturelle-Elemente"></a>
## Strukturelle Formular Elemente

Die strukturellen Formular Elemente werden jeweils durch ihre eigene Methoden eingesetzt, es stehen 4 Methoden zur Verfügung:

* `addTab`
* `closeTab`
* `addFieldset`
* `closeFieldset`

> **Hinweis**
> 
> * `addFieldset` und `addTab` erwartet jeweils Parameter.
> * `closeTab` und `closeFieldset` erwarten keine Parameter.
> * Das Schliessen der Fieldsets oder Tabs ist nicht zwingend nötig.
> * Nicht geschlossen Sturktur-Elemente werden jeweils automatisch am Ende des Formulas geschlossen.
> * Wird ein neues Struktur-Elemente angelegt, werden autoamtisch die davor geöffneten Struktur-Elemente geschlossen.


*Erwarteter Übergabewert der strukturellen Element-Methoden:*


`($value, $attributes)`


* $value => `Element Name`
* $attributes => `array('data-test'=>'test-data')`


> **Hinweis**
>
> * Alle Übergabewerte sind optional.
> * Attribute können nur als Arrays übergeben werden.
> * Attribute stehen nur für öffnende Struktur-Elemente zur Verfügung.
> * Unter Verwendung des Default-Themas sollte man immer nach der Initialisierung der MForm Klasse ein Fieldset anlegen.


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
