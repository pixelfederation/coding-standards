<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Php82;

final class Foo
{
    public function __construct(
        private readonly Bar $bar,
    ) {}

    public function baz(): int
    {
        return $this->bar->width * $this->bar->height;
    }
}

$bar = new Bar(1, 2);
$foo = new Foo($bar);

print $foo->baz();
