<?php

declare(strict_types=1);

use PhpCsFixer\Config;
use PhpCsFixer\Finder;

$finder = new Finder()
    ->in(__DIR__)
    ->exclude('var');

return new Config()
    ->setRiskyAllowed(true)
    ->setRules([
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'strict_comparison' => true,
        'strict_param' => true,
    ])
    ->setFinder($finder);
