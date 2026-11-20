<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__.'/../../public',
        __DIR__.'/../../src',
        __DIR__.'/../../tests',
    ])
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'declare_strict_types' => true,
        'final_class' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'static_lambda' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/.php-cs-fixer.cache')
;
