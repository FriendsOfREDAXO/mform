# MForm - REDAXO Addon

MForm die Erstellung von Modul-Eingaben einfacher. Mit MForm kann man nicht nur flott Formulare erstellen, sondern diese dank flexibler Templates auch optisch genau nach eigenen Wünschen gestalten. Man bekommt alle REDAXO-Standard-Formularelemente und einige Extra-Widgets, die sich spielend leicht in Module einfügen lassen.

**Aber da hört’s noch nicht auf!** 
MForm pimpt auch YForm und rex_form mit zusätzlichen Widgets ordentlich auf. Benutzerdefiniertes Link-Feld oder eine schicke Image-List? Kein Problem, MForm hat die Lösung.

## Neu in Version 8 ###
Und das Highlight in der neuesten Version? Der brandneue Formular-Repeater! Dieses Feature ersetzt das alte MBlock AddOn und bietet die Möglichkeit, Formularelemente nicht nur zu wiederholen, sondern sie auch in mehreren Ebenen zu verschachteln – etwas, das mit MBlock so nicht machbar war. Damit kann man jetzt noch komplexere Formulare aufbauen. 

Die beiliegende **Demo-Sammlung** erlaubt das sofortige Ausprobieren von Modul-Codes. Module können direkt installiert und getestet werden. Die Codes sind alle kommentiert.
Und zuästlich gibt es eine ausführliche Doku 📒.
Um die Liste der Features von MForm in einer logischen und übersichtlichen Reihenfolge zu organisieren, bietet es sich an, sie nach Kategorien wie Basisfunktionalitäten, erweiterte Gestaltungsoptionen und spezielle Widgets oder Funktionen zu gruppieren. Hier ist eine mögliche sortierte und strukturierte Aufstellung:

## Features

### Grundlegende Funktionalitäten
- **Erstellen von Moduleingaben per PHP**: Die Basis, um mit MForm zu arbeiten.
- **Mehrspaltige Formulare**: Layout-Optionen zur Strukturierung der Formulare.
- **Inline-Formular-Elemente**: Für eine kompakte Formulargestaltung.
- **HMTL5-Formular-Elemente**: Nutzung moderner Webstandards.
- **Datalists**: Für verbesserte Eingabeoptionen in Formularen.

### Erweiterte Gestaltung und Interaktivität
- **Custom Widgets für Verlinkung (auch Yform) und Bilder**: Spezielle Widgets für häufig benötigte Funktionen.
- **Factory die es ermöglicht Formularteile leicht auszulagern**: Vereinfacht die Wiederverwendung von Formularkomponenten.
- **Collapse, Tabs, Accordions**: Elemente zur Gestaltung dynamischer, interaktiver Formulare.
- **Wrapper Elemente Via Checkbox, Radio oder Select steuerbare Collapse Elemente**: Bietet interaktive Steuerungselemente für die Benutzerführung.
- **Ausgabe der Formulare anpassbar über Fragmente**: Ermöglicht die flexible Gestaltung der Formulardarstellung.

### Spezielle Funktionen
- **Integrierter Formular-Repeater**: Ersetzt MBlock und erlaubt verschachtelte Formularelemente.
- **REDAXO JSON Value Nutzung**: Integration von REDAXO spezifischen Datenstrukturen.
- **SQL-Felder**: Direkte Einbindung von Datenbankabfragen.
- **Durchgehende MBlock Kompatibilität**: Gewährleistet Kompatibilität mit bestehenden MBlock-Installationen.
- **Modul-Beispiele zur direkten Installation**: Bietet sofort einsatzbereite Vorlagen für verschiedene Anwendungsfälle.

## Formular-Repeater

Der Formular-Repeater seit mform 8 ermöglicht es, Formularelemente dynamisch zu wiederholen und dabei eine Verschachtelung in mehreren Ebenen zu realisieren.

### Migration von MBlock zu MForm 8 

In  Arbeit 
--- 


## Installation

MForm kann direkt über den Redaxo-Installer Installiert werden. [MForm Redaxo Addon Page](http://www.redaxo.org/de/download/addons/?addon_id=967&searchtxt=mform&cat_id=-1)

1. In REDAXO einloggen
2. Im Backend unter "Installer > Neue herunterladen" "MForm" suche und unter "Funktion" "ansehen" klicken
3. Bei der aktuelle Version in der Liste unter "Funktion" "herunterladen" klicken
4. Unter "AddOns" MForm installieren und aktivieren

## Ausgabe

MForm nutzt die von REDAXO bereitgestellten REDAXO Variablen. Entweder als klassische oder als JSON-Values.
Informationen hierzu in der [REDAXO Doku](https://www.redaxo.org/doku/main/redaxo-variablen).

## Lizenz

MForm ist unter der [MIT Lizenz](LICENSE.md) lizensiert.

## Changelog

siehe [CHANGELOG.md](https://github.com/FriendsOfREDAXO/mform/blob/master/CHANGELOG.md)

## Autor

**Friends Of REDAXO**

- <http://www.redaxo.org>
- <https://github.com/FriendsOfREDAXO>

## Credits

**Projekt-Lead**

[Joachim Dörr](https://github.com/joachimdoerr)

**2nd. Maintainer**

[skerbis](https://github.com/skerbis)

**Mform-Repeater**

[Thorben eaCe](https://github.com/eaCe)

**Docs & Testing**

[alexplusde](https://github.com/alexplusde)



