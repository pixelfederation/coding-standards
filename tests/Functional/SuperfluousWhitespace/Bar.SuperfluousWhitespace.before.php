<?php


declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional\SuperfluousWhitespace;

final class Bar
{

    public readonly int $superNumber;


    /**
     * Superfluous docblock parameters
     *
     * @param int $width
     * @param int $height
     */
    public function __construct(
        public readonly int $width ,

        public readonly int $height,
    )
    {
        $total = $this->width + $this->height;


        $heightDoubled = $this->height * 2;
        $this->superNumber = $total + $heightDoubled;
    }

}