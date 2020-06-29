<?php

declare(strict_types=1);

/*
 * @author    mfris
 * @copyright PIXELFEDERATION s.r.o.
 * @license   Internal use only
 */

namespace PixelFederation\CodingStandards\Example\Traits;

use PixelFederation\CodingStandards\Example\Common\SerialNumber;

interface SerialNumberHaving
{
    public function getSerialNumber(): SerialNumber;
}
