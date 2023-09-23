# php-cs-fixer config for REDAXO

### Installation

```
composer require --dev redaxo/php-cs-fixer-config
```

Example `.php-cs-fixer.dist.php`:

```php
<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new Redaxo\PhpCsFixerConfig\Config())
    ->setFinder($finder)
;

```
