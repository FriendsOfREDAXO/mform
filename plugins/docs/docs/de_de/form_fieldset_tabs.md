# Tabs- und Fieldset-Elemente

> ## Inhalt
> - [Strukturelle Formular Elemente](#Strukturelle-Elemente)
> - [Fieldset-Element](#Fieldset-Element)
> - [Tab-Element](#Tab-Element)
> - [Weiterführende Links](#Links)

Durch die Tabs- und Fieldset-Elemente wird die Möglichkeit geboten Formulare klar sturkturieren zu können. In dem Redaxo Bootstrap-default Modul-Formular-Layout und somit auch im MForm Default-Themas sollte man generell Fieldsets einsetzen. 
 

<a name="Strukturelle-Elemente"></a>
## Strukturelle Formular Elemente

Die strukturellen Formular Elemente werden jeweils durch ihre eigene Methoden eingesetzt, es stehen 4 Methoden zur Verfügung:

* `addFieldset`
* `closeFieldset`
* `addTab`
* `closeTab`

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


<a name="Fieldset-Element"></a>
## Fieldset-Element

*Erwartete Übergabewerte der `addFieldset`-Methode:*

`($value, $attributes)`

*Einfaches Fieldset mit Legend Zuweisung und CSS-Class*

```php
$objForm->addFieldset('Fieldset',array('class'=>'myfieldset'));
```

```php
$objForm->closeFieldset();
```


> **Hinweis** 
>
> * Wird ein Fieldset angelegt ist dieses:
>	- a) bis zum Ende des Formulars geöffnet und schließt nach dem letzten Formular-Element oder 
>	- b) geöffnet bis ein anderes Fieldset-Element angelegt wird und umschließt folglich alle Elemente von Fieldset-Element-Methode zu Fieldset-Element-Methode


<a name="Tab-Element"></a>
## Tab-Element

*Erwartete Übergabewerte der `addTab`-Methode:*

`($value, $attributes)`

*Einfacher Tab mit CSS-Class*

```php
$objForm->addTab('Tab',array('class'=>'myfieldset'));
```

```php
$objForm->closeTab();
```

> **Hinweis** 
>
> * Wird ein Tab angelegt ist dieses:
>	- a) bis zum Ende des Formulars geöffnet und schließt nach dem letzten Formular-Element oder 
>	- b) geöffnet bis ein anderes Tab-Element angelegt wird und umschließt folglich alle Elemente von Tab-Element-Methode zu Tab-Element-Methode


<a name="Links"></a>
## Weiterführende Links

*Generell / Allgemein*

* [Elementzuweisungen](elements_general.md)
* [Elementen Attribute zuweisen](elements_attributes.md)
