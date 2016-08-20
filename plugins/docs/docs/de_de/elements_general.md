# Elementzuweisungen


Formular-Elemente können Attribute, Parameter, Optionen oder Validierungen zugewiesen bekommen. Dabei ist es vom Element abhängig ob diese möglich oder nötig sind. Text-Input- oder Hidden-Elemente können beispielsweise mit Attributen versehen werden und Select-Elemente müssen Optionen erhalten.


### Elementen Attribute, Parameter, Optionen und Validierungen zuweisen


Generell sind die Element-Methoden so angelegt, dass Attribute, Parameter, Optionen oder Validierungen durch Arrays an die jeweiligen Konstruktoren übergeben werden können, zudem ist es aber auch möglich diese durch Aufruf ihrer eigenen Methoden hinzuzufügen.


###### Generelle Regel


Hierfür gilt immer die generelle Regel: **Vom erzeugten Element bis zum nächsten werden alle durch ihre Methode eingesetzten Attribute, Parameter, Optionen und Validierungen dem erzeugten Element zugewiesen.**

Elemente für welche Attribute oder Parameter oder Optionen oder Validierungen nicht zulässig sind verarbeiten diese nicht.


### Übergabe-Array


Die Konstruktoren der Methoden `setAttributes`, `setParameter`, `setValidations` und `addOptions` erwarten jeweils ein Übergabe-Array in welchem die Zuweisungen durch Name und Wert übergeben werden.


###### Das Array ist wie folgt aufgebaut:


`array('1_name'=>'1_wert', '2_name'=>'2_wert')`


##### Hinweis:


* In dem Übergabe-Array können beliebig viele Zuweisungen erfasst werden. Doch nur zulässige Werte werden verarbeitet.


### Mögliche Zuweisungen - Detailierte Beschreibungen


* [Elementen Attribute zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Attribute-zuweisen)
* [Elementen Parameter zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Parameter-zuweisen)
* [Elementen Optionen zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Optionen-zuweisen)
* [Elementen Validierungen zuweisen](https://github.com/FriendsOfREDAXO/mform/wiki/Elementen-Validierungen-zuweisen)
* [Default-Values definieren](https://github.com/FriendsOfREDAXO/mform/wiki/Default-Values-definieren)
* [Sonstige Zuweisungen](https://github.com/FriendsOfREDAXO/mform/wiki/Sonstige-Zuweisungen)