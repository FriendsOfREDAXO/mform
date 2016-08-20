# MForm - Redaxo Addon für Modul-Input-Formulare


### Was ist MForm


MForm ist ein Redaxo Addon, welches das Erstellen von **Modul-Input-Formularen** erheblich erleichtert. Dabei nutzt MForm Templates welche es dem Administrator ermöglichen den Modul-Style seinen Vorstellungen anzupassen. MForm stellt alle wesentlichen Modul-Input-Formular-Elemente bereit welche sich durch die Methoden der MForm-Klasse recht einfach einbinden lassen. Dabei bietet MForm weitere Extras, welche sonst in den Modul-Input-Formularen von Redaxo nur mit erhöhtem Aufwand integriert werden können, wie Multiselect-Boxen oder Formular Validierungen.


### Wiki-Inhalt und Ziel


In diesem Wiki sind detaillierte Beschreibungen zur Nutzung von MForm und zur Erstellung von Modul-Input-Formulare mit den einzelnen Methoden der MForm-Klasse zu finden.

Dem erfahrenen Entwickler als auch dem Einsteiger soll dieses Wiki die Handhabung verdeutlichen und Hilfestellung bei allen zu Thema MForm anfallenden Fragen geben.


### MForm für Redaxo 4 und 5


MForm wird sowohl für Redaxo 4.5 & < als auch für Redaxo 5 bereitgestellt und entwickelt. Die hier im Wiki enthaltene Informationen sollten für beide Versionen Gültigkeit haben, unterschiede in der Handhabung zwischen diesen Versionen, welche es durch Neuerungen zwischen den Redaxo-Versionen gibt, werden sofern vorhanden entsprechend besprochen und gekennzeichnet.


*** 


##### Hinweis:


MForm ist ausschließlich zum erstellen von **Modul-Input-Formularen** geeignet. Es ist nicht gedacht um damit Frontend- oder Addon-Formulare zu erzeugen.


##### Wichtig:


Ab Version 2.2.0 benötigt MForm keine `REX_VALUE[x]`-Übergabe mehr, diese Übergabepflicht wurde komplett entfernt. Da MForm ab der Version 2.2.0 die ab Redaxo 4.5 möglichen `REX_VALUE-ARRAYs` unterstütz gibt es praktisch keine `REX_VALUE`-Limitierung mehr.