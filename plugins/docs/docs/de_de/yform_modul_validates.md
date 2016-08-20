# YForm-Modul: Validierung

> ## Inhalt
> - [Zweck der Validierungen](#zweck-der-validierungen)
> - [Validierungs-Klassen](#validierungs-klassen)
> 	- [compare](#compare)
> 	- [compare_value](#compare-value)
> 	- [customfunction](#customfunction)
> 	- [email](#email)
> 	- [empty](#empty)
> 	- [intfromto](#intformto)
> 	- [labelexist](#labelexist)
> 	- [preg_match](#preg_match)
> 	- [size](#size)
> 	- [size_range](#size_range)
> 	- [type](#type)
> 	- [unique](#unique)

	
<a name="zweck-der-validierungen"></a>
## Zweck der Validierungen

Mit diesen Klassen lassen sich Values überprüfen. Bei einer negativen Validierung wird eine entsprechende Warnung ausgegeben.

Die Validate-Feldklassen werden wie Values und Actions im Formbuilder im Feld `Felddefinitonen` eingetragen. Dabei muss immer der Name der Value-Feldklasse angegeben, der validiert werden soll.

> Die Validate-Klassen sind hier zu finden:  
> `src/addons/yform/lib/yform/validate/`

Die PHP-Beispiele können in diesem Basis-Formular getestet/eingesetzt werden:

	<?php
	$yform = new rex_yform();
	$yform->setObjectparams('form_action', rex_getUrl(REX_ARTICLE_ID,REX_CLANG_ID));

	$yform->setValueField('text', array("wert1","Wert 1"));
	$yform->setValidateField('empty', array("wert1","Bitte geben Sie einen Namen an!"));
	
	echo $yform->getForm();
	?>

<a name="validierungs-klassen"></a>
## Validierungs-Klassen

<a name="compare"></a>
### compare

Vergleicht zwei Felder mit Hilfe von Operatoren.

	// allgemeine Definition
	validate|compare|label1|label2|[!=,<,>,==,>=,<=]|warning_message|

	// im YForm-Formbuilder
	text|wert1|Wert 1|
	text|wert2|Wert 2|
	validate|compare|wert1|wert2|!=|Die beiden Felder haben unterschiedliche Werte|

	// in PHP
	$yform->setValueField('text', array("wert1","Wert 1"));
	$yform->setValueField('text', array("wert2","Wert 2"));
	$yform->setValidateField('compare', array("wert1","wert2","!=", "Die Felder haben unterschiedliche Werte"));
	
> **Hinweis:** Mögliche Vergleichs-Operatoren sind `!=`, `<`, `>`, `==`, `>=`und `<=`

<a name="compare-value"></a>
### compare_value

Vergleicht ein Feld mit einem angegebenen Wert mit Hilfe von Operatoren.

	// Definition
	validate|compare_value|label|value|[!=,<,>,==,>=,<=]|warning_message
	
	// Im YForm-Formbuilder
	text|wert1|Wert 1|
	validate|compare_value|wert1|2|<|Der Wert ist kleiner als 2!|

	// In PHP
	$yform->setValueField('text', array("wert1","Wert 1"));
	$yform->setValidateField('compare_value', array("wert1","wert2","<", "Der Wert ist kleiner als 2!"));
	
> **Hinweis:** Mögliche Vergleichs-Operatoren sind `!=`, `<`, `>`, `==`, `>=` und `<=`

<a name="customfunction"></a>
### customfunction

Damit können eigene Überprüfungen via Funktion oder Klasse/Methode durchgeführt werden.

	// Definition
	validate|customfunction|label|[!]function/class::method|weitere_parameter|warning_message
	
<a name="email"></a>
### email

Überprüft, ob die Feldeingabe eine E-Mail-Adresse ist. Ein leere Eingabe würde dabei als korrekt bewertet werden! Es empfiehlt sich also eine zusätzliche Validiierung mit `empty`.

	// Definition
	validate|email|emaillabel|warning_message
	
	// Im YForm-Formbuilder
	text|email|E-Mail|
	validate|email|email|Das Feld enthält keine korrekte E-Mail-Adresse!

	II In PHP
	$yform->setValueField('text', array("email","E-Mail"));
	$yform->setValidateField('email', array("email", "Das Feld enthält keine korrekte E-Mail-Adresse!"));

<a name="empty"></a>
### empty

Überprüft, ob im Feld ein Wert eingetragen wurde und gibt ein Meldung aus.

	// Definition
	validate|empty|label|Meldung

	// Im YForm-Formbuilder
	text|name|Nachname|
	validate|empty|name|Bitte geben Sie einen Namen an!

	// In PHP

	$yform->setValueField('text', array("name","Nachname"));
	$yform->setValidateField('empty', array("name","Bitte geben Sie einen Namen an!"));

<a name="existintable"></a>
### existintable (wird nicht mehr fortgeführt)

Überprüft, ob ein Feld in einer Tabelle existiert.
 
	// Definition
	validate|existintable|label,label2|tablename|feldname,feldname2|warning_message

<a name="infromto"></a>  
### intfromto

Überprüft ob der **Wert** der Eingabe größer oder kleiner als die definierten Werte sind.

	// Definition
	validate|intfromto|label|from|to|warning_message
	
	// Im YForm-Formbuilder
	text|wert|Wert
	validate|intfromto|wert|2|4|Der Wert ist kleiner als 2 und größer als 4! 

	// In PHP
	$yform->setValueField('text', array("wert","Wert"));
	$yform->setValidateField('intfromto', array("wert","2", "4", "Der Wert ist kleiner als 2 und größer als 4! "));

<a name="labelexist"></a>
### labelexist

Überprüft mit einem Minimal- und Maximalwert, ob eine bestimmte Menge an Feldern ausgefüllt wurden.

	// Definition
	validate|labelexist|label,label2,label3|[minlabels]|[maximallabels]|Fehlermeldung
	
	// Im YForm-Formbuilder
	text|vorname|Vorname|
	text|name|Nachname|
	text|email|E-Mail|
	text|tel|Telefon|
	
	validate|labelexist|vorname,name,tel|1|2|Fehlermeldung

	In PHP	$yform->setValueField('text', array("vorname","Vorname"));
	$yform->setValueField('text', array("name","Nachname"));
	$yform->setValueField('text', array("email","E-Mail"));
	$yform->setValueField('text', array("tel","Telefon"));
	
	$yform->setValidateField('labelexist', array("vorname, name, tel", "1", "2", "Fehlermeldung"));
	
	// Hier in diesem Beispiel müssen von den drei Feldern mindestens 1 und maximal 2 ausgefüllt werden

> Hinweis:  
> * `minlabels` ist optional und hat den Defaultwert 1.  
> * `maximallabels` ist optional und den Defaultwert 1000.

<a name="preg_match"></a>
### preg_match

Überprüft die Eingabe auf die hinterlegten Regex-Regeln.

	// Definition
	validate|preg_match|label|/[a-z]/i|warning_message
	
	// Im YForm-Formbuilder
	text|eingabe|Eingabe
	validate|preg_match|eingabe|/[a-z]+/|Es dürfen nur ein oder mehrere klein geschriebene 	Buchstaben eingegeben werden!

	// In PHP
	$yform->setValueField('text', array("eingabe","Eingabe"));
	$yform->setValidateField('preg_match', array("eingabe","/[a-z]+/", "Es dürfen nur ein oder mehrere klein geschriebene Buchstaben eingegeben werden!"));

<a name="size"></a>
### size

Überprüft die Eingabe eines Feldes auf genau die angegebene Zeichenlänge.

	// Definition
	validate|size|plz|[size]|warning_message
	
	// Im YForm-Formbuilder
	text|plz|PLZ
	validate|size|plz|5|Die Eingabe hat nicht die korrekte Zeichenlänge!

	// In PHP
	$yform->setValueField('text', array("plz","PLZ"));
	$yform->setValidateField('size', array("plz","5", "Die Eingabe hat nicht die korrekte Zeichenlänge!"));

> **Hinweis:** `size` ist eine Zahl und meint die Zeichenlänge.

<a name="size_range"></a>
### size_range

Überprüft die Eingabe eines Feldes auf die angegebene **Zeichenlänge**, die zwischen dem Minimal- und Maximalwert liegt

	// Definition
	validate|size_range|label|[minsize]|[maxsize]|Fehlermeldung
	
	// Im YForm-Formbuilder
	text|summe|Summe
	validate|size_range|summe|3|10|Die Eingabe hat nicht die korrekte Zeichenlänge (mind. 3, max 10 Zeichen)!

	// In PHP
	$yform->setValueField('text', array("summe","Summe"));
	$yform->setValidateField('size_range', array("summe", "3", "10", "Die Eingabe hat nicht die korrekte Zeichenlänge (mind. 3, max 10 Zeichen)!"));

<a name="type"></a>
### type

Überprüft den Typ der Eingabe.

	// Definition
	validate|type|label|[int,float,numeric,string,email,url,date,datetime]|Fehlermeldung|[1 = Feld darf auch leer sein]	
	
	// Im YForm-Formbuilder
	text|wert|Wert
	validate|type|wert|numeric|Die Eingabe ist keine Nummer!

	// In PHP
	$yform->setValueField('text', array("wert","Wert"));
	$yform->setValidateField('type', array("wert", "numeric", "Die Eingabe ist keine Nummer!"));

<a name="type"></a>
### unique

Überprüft, ob ein Datensatz mit einem bestimmten Feld-Wert bereits in einer Datenbank-Tabelle vorhanden ist.

	// Definition
	validate|unique|dbfeldname[,dbfeldname2]|Dieser Name existiert schon|[table]
	
	// Im YForm-Formbuilder
	text|email|E-Mail|
	validate|unique|email|Ein User mit dieser E-Mail-Adresse existiert schon!|rex_user

	// In PHP
	$yform->setValueField('text', array("email","E-Mail"));
	$yform->setValidateField('unique', array("email", "Ein User mit dieser E-Mail-Adresse existiert schon!|rex_user"));

> **Hinweise:**  
> * `table`: Wenn kein Tabellenname angegeben ist, wird der Tabellenname verwendet, der im Formbuilder ausgewählt wurde.  
> * `dbfeldname`: Es können mehrere Feldname überprüft werden (kommagetrennt).