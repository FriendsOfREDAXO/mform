# MForm - REDAXO Addon für Modul-Input-Formulare

### Version 5.2.0

* Javascript für Multipe Selects entfernt, dafür nötiges Hidden-Input ebenfalls entfernt. 
    * **Zu beachten bei Updates**:
        * Ein Hidden-Input Feld welches Komma-separiert die selected-options aufnimmt gibt es nicht mehr.
        * Multiple Selects werden künftig als JSON-String direkt im REX_VALUE gespeichert.
        * Dies Wirkt sich auf die Auswertung der REX_VALUES im Modul-Output aus.
        * Künftig muss für diese REX_VALUES `rex_var::toArray` genutzt werden um die JSON-Strings in Arrays zu decodieren.
        * Beim editieren alter REX_VALUES gehen keine zuordnungen verloren, beim erneuten Speichern wird im neuen Format gespeichert.
        * Im DB Column des REX_VALUES wird aus dem String `1,2` der JSON-String `["1","2"]`
* Docu Plugin hinzugefügt
    * Das alte MForm Github Wiki wurde in das Docu-Plugin übernommen
    * Alle Inhalte wurden überarbeitet
    * Thanks Alexander Walther, Paul Götz, Tim Filler
* Bootstrap Tabs integriert
* Selected und Checked haben einen Leerzeichen-Prefix erhalten.
* EN-Sprachdatei wurde übersetzt    
    * Thanks Thomas Skerbis, ynamite 
