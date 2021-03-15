# Custom-Link Element

Das Custom MForm Custom-Link-Element ermöglicht es durch den Einsatz eines Feldes mehrere Link-Typen definieren zu können.  
Das Cusotm-Link-Element steht in MForm, YForm und auch als REX_VAR zur Verfügung.  

Die Link Typen des Custom-Link-Elements:

* `data-extern`
* `data-intern`
* `data-media`
* `data-mailto`
* `data-tel`
* `ylink`

Jeder dieser Typen kann aktiviert oder deaktiviert werden. Per default sind folgende Typen aktiv:

* `data-extern`
* `data-intern`
* `data-media`


## Verwendung mit MBlock

Das Custom-Link-Element darf keinen String (wie bei anderen Elementen) in der ID enthalten:  

`$MBlock->addCustomLinkField("$id.0.1",array('label'=>'Link'));`


## Beispiel-Code: 

### MForm

```php
$mform = new MForm();
$ylink = [['name' => 'Countries', 'table'=>'rex_ycountries', 'column' => 'de_de']];
$mform->addCustomLinkField(1, ['label' => 'custom', 'data-intern'=>'disable', 'data-extern'=>'enable', 'ylink' => $ylink]);
echo $mform->show();
```

### Als REX_VAR

```html
REX_CUSTOM_LINK[id=5 widget=1 external=1 intern=0 mailto=0 phone=1 media=1 ylink="Countries::rex_ycountries::de_de,CountriesEN::rex_ycountries::en_gb"]
```


### Auslesen der Ylinks

```php 
$link = explode("://", $img['link']);

      if (count($link) > 1) {
          // its a table link
          // url AddOn
        $url = rex_getUrl('', '', [$link[0] => $link[1]]); // key muss im url addon übereinstimmen
      } else {
          $extUrl = parse_url($link[0]);

          if (isset($extUrl['scheme']) && ($extUrl['scheme'] == 'http' || $extUrl['scheme'] == 'https')) {
              // its an external link 
              $url = $link[0];
          } else {
              // internal id
              $url = rex_getUrl($link[0]);
          }
      }
```
