<?php

declare(strict_types=1);

/*
 * @author    mfris
 * @copyright PIXELFEDERATION s.r.o.
 * @license   Internal use only
 */

namespace PixelFederation\CodingStandards\Example;

use PixelFederation\CodingStandards\Example\Common\HardwareId;

interface Hardware extends Traits\SerialNumberHaving, Traits\ServiceTagHaving
{
    /**
     * Get Hardare Id
     *
     * @return int
     */
    public function getId(): HardwareId;
}
