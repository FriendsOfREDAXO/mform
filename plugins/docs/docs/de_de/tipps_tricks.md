# Tipps & Tricks

## Eingenes Theme

Eigenes Theme anlegen im Data-Ordner `redaxo/data/addons/mform/templates`.
Als Grundlage kann man das default_theme verwenden. 

Im Projekt-AddOn kann man das neue Theme in der boot.php festlegen:  

```php
$package = rex_addon::get('mform');
$package->setConfig('mform_theme', 'dein_theme');
```
