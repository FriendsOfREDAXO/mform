<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
;

return (new Redaxo\PhpCsFixerConfig\Config())
    ->setFinder($finder)
;
