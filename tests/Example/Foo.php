<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Example;

final readonly class Foo
{
    public function __construct(
        private Bar $bar,
    ) {}

    public function multiply(): int
    {
        return $this->bar->width * $this->bar->height;
    }
}
