# E-Mail-Templates erstellen

> ##Hinweis:
> - [Zweck der E-Mail-Templates](#zweck-der-email-templates)
> - [Handhabung](#handhabung)
> 	- [Beispiel im Formbuilder](#beispiel-formbuilder)  
> 	- [Eingaben im E-Mail-Template](#email-template)
> 	- [PHP](#php)
	
<a name="zweck-der-email-templates"></a>
## Zweck der E-Mail-Templates

Will man eine E-Mail aus einem YForm-Formular versenden, kann man mit Hilfe eines `E-Mail-Templates` (siehe entsprechender Menüpunkt in YForm) diese E-Mail gestalten und mit Platzhaltern aus dem Formular versehen.

Über die E-Mail-Template-Verwaltung kann ein Template angelegt werden. Dabei muss zuerst ein Key erstellt werden, der die eindeutige Zuordnung zu diesem Tempalte ermöglicht. Ebenfalls muss die Absender-E-Mail, der Ansender-E-Mail-Name sowie der Betreff eingegeben werden.

Danach folgen die Eingaben für den E-Mail-Body, in Plain und HTML (optional).

<a name="handhabung"></a>
## Handhabung

Über die Aktion **tpl2email** kann eine E-Mail über den angebenen **Key** eines E-Mail-Templates gesendet werden. Über das Formular können zb die Werte der beiden Eingabefelder des Formular über das E-Mail-Template ausgeben werden.

<a name="beispiel-formbuilder"></a>
### Beispiel-Formular im Formbuilder

	text|vorname|Vorname|
	text|name|Name|
	text|email|E-Mail-Adresse|

	validate|email|email|Das Feld enthält keine korrekte E-Mail-Adresse!
	validate|empty|email|Das Feld enthält keine korrekte E-Mail-Adresse!
	
	action|tpl2email|testtemplate|email

<a name="email-template"></a>
### Eingaben im E-Mail-Template

Als E-Mail-Template `Key` wird eingetragen:

	testtemplate

In den E-Mail-Template `Body` kommt:
	
	Hallo,
	REX_YFORM_DATA[field="vorname"] REX_YFORM_DATA[field="name"]
	
In den E-Mail-Template `Body (Html)` kommt:
	
	Hallo,<br />
	REX_YFORM_DATA[field="vorname"] REX_YFORM_DATA[field="name"]

<a name="php"></a>		
### PHP

Es kann auch PHP-Code intergriert werden, um z.B. Formular-Eingaben zu prüfen und die Ausgabe in der E-Mail individuell zu verändern.
	
	
	Hallo,<br />
	<?php 
	if ("REX_YFORM_DATA[field="anrede"]" == "w") echo "Frau";
	else echo "Herr";
	?> REX_YFORM_DATA[field="vorname"] REX_YFORM_DATA[field="name"]
		
> **Hinweis:**  
> Die Action **tpl2email** kann auch mehrfach im Formular eingesetzt werden. So könnten E-Mails mit unterschiedlichen Templates versendet werden oder auch an mehrere Empfänger, z.B. Admin unhd Kunde.





	
	
