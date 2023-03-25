<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Php74;

use PixelFederation\CodingStandards\Example\Php74;
use PixelFederation\CodingStandards\Example\Php74\Common\HardwareId;

interface Hardware extends Php74\Traits\SerialNumberHaving, Php74\Traits\ServiceTagHaving
{
    public function getId(): HardwareId;
}
