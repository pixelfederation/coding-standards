<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Example;

final readonly class Bar
{
    public function __construct(
        public int $width,
        public int $height,
    ) {}
}
