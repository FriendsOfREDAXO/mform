<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/lib',
        __DIR__ . '/pages',
    ])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(false)
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder);
