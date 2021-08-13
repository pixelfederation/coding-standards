<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example;

use PixelFederation\CodingStandards\Example\Common\HardwareId;

interface Hardware extends Traits\SerialNumberHaving, Traits\ServiceTagHaving
{
    public function getId(): HardwareId;
}
