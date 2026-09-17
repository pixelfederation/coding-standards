<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional\SuperfluousWhitespace;

final readonly class Bar
{
    public int $superNumber;

    /**
     * Superfluous docblock parameters
     */
    public function __construct(
        public int $width,
        public int $height,
    ) {
        $total = $this->width + $this->height;

        $heightDoubled = $this->height * 2;
        $this->superNumber = $total + $heightDoubled;
    }
}
