# YForm-Modul: Actions

> ## Inhalt
> - [Zweck der Aktionen](#zweck-der-aktionen)
> - [Action-Klassen](#action-klassen)
>	- [callback](#callback)
>	- [copy_value](#copy_value)
>	- [createdb](#createdb)
>	- [db](#db)
>	- [db_query](#db_query)
>	- [email](#email)
>	- [encrypt_value](#encrypt_value)
>	- [fulltext_value](#fulltext_value)
>	- [html](#html)
>	- [readtable](#readtable)
>	- [reditect](#redirect)
>	- [showtext](#showtext)
>	- [tpl2email](#tpl2email)
>	- [wrapper_value](#wrapper_value)
	
<a name="zweck-der-aktionen"></a>
## Zweck der Aktionen

Aktionen defineren, was nach dem Versand des Formulars mit den Formulardaten passieren soll, z.B. der Versand einer E-Mail über ein E-Mail-Template oder die Speicherung der Daten in einer Tabelle.


> Die Action-Klassen sind hier zu finden:  
> `src/addons/yform/lib/yform/actions/`


Beispiele (Schreibweisen) gibt es für **yForm Formbuilder** und **PHP**

Die PHP-Beispiele können in diesem Formular getestet/eingesetzt werden:

	<?php
	$yform = new rex_yform();
	$yform->setObjectparams('form_action', rex_getUrl(REX_ARTICLE_ID,REX_CLANG_ID));
	
	$yform->setValueField('text', array("wert1","Wert 1"));
	$yform->setValidateField('empty', array("wert1","Bitte geben Sie einen Namen an!"));
	
	echo $yform->getForm();
	?>
	
<a name="action-klassen"></a>
## Action-Klassen

<a name="callback"></a>
### callback

Ruf eine Funktion oder Klasse auf.

	// allgemeine Definition
	action|callback|mycallback / myclass::mycallback

	// im YForm-Formbuilder
	folgt ...
	
	// in PHP
	folgt ...

<a name="copy_value"></a>
### copy_value

Kopiert Eingaben vom Feld mit dem Label `label_from` in das Feld mit dem Label `label_to`

	// allgemeine Definition
	action|copy_value|label_from|label_to
	
	// im YForm-Formbuilder
	hidden|user
	text|name|Name
	action|copy_value|name|user
	
	action|db|rex_warenkorb
	action|html|Daten gespeichert	
	
	// in PHP	
	$yform->setValueField('hidden', array("user"));
	$yform->setValueField('text', array("name","Name"));
	$yform->setActionField('copy_value', array("name","user"));
	
	$yform->setActionField('db', array("rex_warenkorb"));
	$yform->setActionField('html', array("Daten gespeichert"));

<a name="createdb"></a>
### createdb

Erstellt eine Datenbank-Tabelle. Formular-Label werden dabei als Feldnamen in die neue Tabelle gespeichert. Die neue Tabelle erscheint dabei **nicht** in der Redaxo-Tabellen-Struktur.

	// allgemeine Definition
	action|createdb|tblname

	// Beispiel Formbuilder
	text|vorname|Vorname
	text|name|Name
	
	action|createdb|shop_user
	
	// in PHP
	$yform->setValueField('text', array("vorname","Vorname"));
	$yform->setValueField('text', array("name","Name"));

	$yform->setActionField('createdb', array("shop_user"));

<a name="db"></a>
### db

Speichert oder aktualisiert Formulardaten in einer Tabelle. Dabei werden die Label und deren Eingaben in die gleichnamigen Tabellenfelder gespeichert.

	// allgemeine Definition
	action|db|tblname|[where(id=2)/main_where]

	im YForm-Formbuilder
	text|vorname|Vorname
	text|name|Name
	text|plz|PLZ
	text|ort|Ort

	action|db|rex_warenkorb
	action|html|Daten gespeichert	

	// in Beispiel PHP
	$yform->setValueField('text', array("vorname","Vorname"));
	$yform->setValueField('text', array("name","Name"));
	$yform->setValueField('text', array("plz","PLZ"));
	$yform->setValueField('text', array("ort","Ort"));

	$yform->setActionField('db', array("rex_warenkorb"));
	$yform->setActionField('html', array("Daten gespeichert"));

<a name="db_query"></a>
### db_query

Führt eine Abfrage aus, z.B. um hier Werte aus Eingabefeldern in die Abfrage einzusetzen.

	// allgemeine Definition
	action|db_query|query|Fehlermeldung

	// im YForm-Formbuilder
	text|name|Name
	text|email|E-Mail-Adresse
	action|db_query|insert into rex_ycom_user set name = ?, email = ?|name,email

	// in PHP
	$yform->setValueField('text', array("name","Name"));
	$yform->setValueField('text', array("email","|E-Mail-Adresse"));
	$yform->setActionField('db_query', array("insert into rex_ycom_user set name = ?, email = ?", "name,email"));

<a name="email"></a>
### email

Sendet E-Mail mit Betreff und Body an angegebene E-Mail-Adresse. Eingaben aus dem Formular können als Platzhalter im Mailbody verwendet werden. 

	// allgemeine Definition
	action|email|from@email.de|to@email.de|Mailsubject|Mailbody###name###
	
	im YForm-Formbuilder
	text|name|Name
	action|email|from@mustermann|to@mustermann.de|Test|Hallo ###name###
	

	// in Beispiel PHP
	$yform->setValueField('text', array("name","Name"));
	$yform->setActionField('email', array("from@mustermann", "to@mustermann.de", "Test", "Hallo ###name###"));

<a name="encrypt_value"></a>
### encrypt_value (wird nicht mehr fortgeführt)

Verschlüsselt eine Eingabe in Feld mit Label.

	// allgemine Definition
	action|encrypt|label[,label2,label3]|md5|[save_in_this_label]

	im YForm-Formbuilder
	text|pass|Password
	action|encrypt_value|pass|md5

	action|db|rex_warenkorb
	action|html|Daten gespeichert	
	
	// In Beispiel PHP
	$yform->setValueField('text', array("pass", "Password"));
	$yform->setActionField('encrypt_value', array("pass", "md5"));	
	$yform->setActionField('db', array("rex_warenkorb"));
	$yform->setActionField('html', array("Daten gespeichert"));

<a name="fulltext_value"></a>
### fulltext_value (wird nicht mehr fortgeführt)

Erklärung folgt.

	// allgemine Definition
	action|fulltext_value|label|fulltextlabels with ,

	// im YForm-Formbuilder
	folgt 
	
	// in PHP
	folgt

<a name="html"></a>
### html

Gibt HTML-Code aus.

	// allgemeine Definition
	action|html|[html]

	// im YForm-Formbuilder
	action|html|<b>fett</b>
	
	// In PHP
	$yform->setActionField('html', array("<b>fett</b>"));

<a name="readtable"></a>
### readtable

Liest aus der angegebenen Tabelle den Feldinhalt von `feldname` anhand der Eingabe im Formular-Feld `label` den gefundenen Datensatz. Das gesuchte Tabellen-Feld `label`muss im Formular vorhanden sein.  
Damit kann man anhand eines Eingabefeldes Daten aus einer Tabellen selektieren. Die Werte des gefundenen Datensatzes stehen dann auch zur Weiterverarbeitung z.B. im E-Mail-Versand zur Verfügung.

	// allgemeine Definition
	action|readtable|tablename|feldname|label

	// im YForm-Formbuilder
	text|name|Name
	action|readtable|shop_user|fname|name
	
	// In PHP
	$yform->setValueField('text', array("name","Name"));
	$yform->setActionField('readtable', array("shop_user", "fname", "name"));

<a name="redirect"></a>
###redirect

Führt nach dem Abschicken des Formulars eine Weiterleitung aus.
	
	// allgemeine Definition
	action|redirect|Artikel-Id oder Externer Link|request/label|field

	// im YForm-Formbuilder
	//Umleitung auf internen Artikel 32
	action|redirect|32  	
	
	// In PHP
	$yform->setActionField('redirect', array("32"));

<a name="showtext"></a>
###showtext

Gibt einen Antworttext zurück, der als Plaintext, HTML oder über Textile formatiert werden kann.

	// allgemeine Definition
	action|showtext|Antworttext|<p>|</p>|0/1/2 (plaintext/html/textile)

	// im YForm-Formbuilder
	action|showtext|Hallo das ist Redaxo|<p>|</p>|0
	action|showtext|Hallo das ist *Redaxo*|||2
	
	// In Beispiel PHP
	$yform->setActionField('showtext', array("Hallo das ist Redaxo", "<p>", "</p>", "0"));
	$yform->setActionField('showtext', array("Hallo das ist *Redaxo*", "", "", "2"));

	// Ausgabe nach Submit
	<p>Hallo das ist Redaxo</p>	
	<p>Hallo das ist <strong>Redaxo</strong></p>

<a name="tpl2email"></a>
### tpl2email (Plugin email)

Versendet eine E-Mail über ein YForm-E-Mail-Template. Der Parameter **emailtemplate** ist der Key des E-Mail-Templates.

	// allgemeine Definition
	action|tpl2email|emailtemplate|emaillabel|[email@domain.de]

	// im YForm-Formbuilder
	text|email|E-Mail-Empfänger
	action|tpl2email|emailtemplate|email

	// In Beispiel PHP
	$yform->setValueField('text', array("email","E-Mail-Empfänger"));  	$yform->setActionField('tpl2email', array("emailtemplate", "email"));

> **Hinweis:**
> * Wird keine E-Mail-Adresse angegeben, wird die E-Mail-Adresse verwendet, die bei `System/Einstellungen` hinterlegt ist.
> * `emaillabel` ist das E-Mail-Label, Formular-Element
> * Wird eine E-Mail-Adresse angegeben, wird die E-Mail des Labels überschrieben.
	
<a name="wrapper_value"></a>
### wrapper_value (wird nicht mehr fortgeführt)

	// allgemeine Definition
	action|wrapper_value|label|prefix###value###suffix
	
	// im YForm-Formbuilder
	text|telefon|Telefon
	action|wrapper_value|telefon|<a href="tel:+49###value###">###value###</a>
	action|db|rex_warenkorb
	action|html|Daten gespeichert	

	// In Beispiel PHP
	$yform->setValueField('text', array("telefon", "Telefon"));
	$yform->setActionField('wrapper_value', array("telefon", "<a href="tel:+49###value###">###value###</a>"));
	$yform->setActionField('db', array("rex_warenkorb"));
	$yform->setActionField('html', array("Daten gespeichert"));
