<?php

$finder = PhpCsFixer\Finder::create()
    ->in([
        __DIR__ . DIRECTORY_SEPARATOR . 'src',
        __DIR__ . DIRECTORY_SEPARATOR . 'tests',
    ])
    ->name('*.php')
;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules([
        '@PHP56Migration' => true,
        '@Symfony' => true,
        'concat_space' => ['spacing' => 'one'],
        'yoda_style' => null,
    ])
    ->setFinder($finder)
;
