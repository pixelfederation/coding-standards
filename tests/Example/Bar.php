<?php

declare(strict_types=1);

namespace Tests\Example;

final class Bar
{
    public function __construct(
        public readonly int $width,
        public readonly int $height,
    ) {}
}
