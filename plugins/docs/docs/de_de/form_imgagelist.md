# Imagelist Element

Das ImageList Element erlaubt es eine visuelle Imagelist zu pflegen. Es befüllt bei Verwendung eine `REX_MEDIALIST`   

Anwendung in mform: 

```php
$mform->addImagelistField(1, array('label' => 'Bilder'));
```

## Einsatz als REDAXO Variable

`REX_IMGLIST[id=1 widget=1] `

In diesem Fall wird eine REX_VALUE[] befüllt. Es empfiehlt sich daher die Verwendung über mform. 
