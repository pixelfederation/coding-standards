<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Traits;

use PixelFederation\CodingStandards\Example\Common\SerialNumber;

interface SerialNumberHaving
{
    public function getSerialNumber(): SerialNumber;
}
