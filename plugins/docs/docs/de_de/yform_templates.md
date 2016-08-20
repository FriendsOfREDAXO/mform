# YForm-Templates

- [Zweck der Templates](#zweck-der-templates)
- [Handhabung](#handhabung)

	

## Zweck der Templates

•	Kontaktformular
•	Anfrage Preisliste
•	Mehrsprachiges Kontaktformular
•	Unterschiedliche Mails an Empfänger und Versender
•	Text-Version aus der HTML-Version (mit strip_tags)

## Handhabung

-	Vor Versand umformatieren
-	Zeitstempel, 
-	dynamisches Textfeld, das via REQUEST befüllt wurde, 
-	Text des Feldes, das via SELECT_SQL befüllt wurde.
-	Fileuploads mitversenden ($mail->AddAttachment($bild, $sql->getValue("perso")); )
