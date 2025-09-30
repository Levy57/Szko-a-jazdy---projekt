<?php
//COMMAND vendor/bin/php-cs-fixer fix

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['vendor', 'tests'])
    ->name('*.php');

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true, 
        //'array_syntax' => ['syntax' => 'short'],
    ])
    ->setFinder($finder);
