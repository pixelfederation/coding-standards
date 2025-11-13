<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Example;

require_once dirname(__DIR__, 2) . '/vendor/autoload.php';

$bar = new Bar(2, 3);
$foo = new Foo($bar);

print $foo->multiply();
