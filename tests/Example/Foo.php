<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Example;

final class Foo
{
    public function __construct(
        private readonly Bar $bar,
    ) {}

    public function multiply(): int
    {
        return $this->bar->width * $this->bar->height;
    }
}
