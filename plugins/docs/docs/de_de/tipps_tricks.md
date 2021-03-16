# Tipps & Tricks

## Eigenes Theme

Eigenes Theme anlegen im Data-Ordner `redaxo/data/addons/mform/templates`.
Als Grundlage kann man das default_theme verwenden. 

Im Projekt-AddOn kann man das neue Theme in der boot.php festlegen:  

```php
$package = rex_addon::get('mform');
$package->setConfig('mform_theme', 'dein_theme');
```

## Alternative Funktion zum Auslesen des Customlinks 

#### Custom Link auslesen

> *Hinweis:* In ist eine solche Methode bereits integriert (prepareCustomLink). Der nachfolgende Code kann aber dafür verwendet werden, um eigene Lösungen zu entwickeln.

Die nachfolgende Funktion dient dazu den von MForm / Mblock generierten CustomLink auszulesen und korrekt zu verlinken. Die Funktion kann in der Ausgabe eines Moduls genutzt werden oder ggf. im Theme- oder Projektaddon verwendet werden. Sie kann auch allgemein dazu verwendet werden, einen unbekannten Link zu identifizieren 

Die Funktion kann in der functions.php vom theme-AddOn oder in der boot.php vom project-AddOn hinterlegt werden:  

```php
// CustomLink-Funktion REX5 / mform / mblock

if (!function_exists('getcustomLink')) {
  function getcustomLink($url,$text) {

  // Wurde ein Wert für $url übergeben?
  if ($url) {

    // Prüfe ob es sich um eine URL handelt, dann weiter
    if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
    }

    // Ist es eine Mediendatei?
    if (file_exists(rex_path::media($url)) === true) {
       $url = rex_url::media($url);
    }
    else {
        // Ist es keine Mediendatei oder URL, dann als REDAXO-Artikel-ID behandeln
        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE and is_numeric($url)) {
            $url = rex_getUrl($url);
        }
    }

    // wurde ein Linktext übergeben?
    if ($text!='') {
        $linkText = $text;
    }
    else {
      $linkText = 'Es wurde kein Linktext oder Inhalt übergeben';
    }

    // Beipiel für die Rückgabe , gerne selbst anpassen
    $link = '<a class="link" href="'.$url.'">'.$linkText.'</a>';
    return $link;
   }
  }
}
```

## Anwendungsbeispiel

```php
echo getcustomLink($url='10',$text='Hallo ich bin ein Link');
```
