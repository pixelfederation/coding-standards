<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Php74\Traits;

use PixelFederation\CodingStandards\Example\Php74\Common\SerialNumber;

interface SerialNumberHaving
{
    public function getSerialNumber(): SerialNumber;
}
