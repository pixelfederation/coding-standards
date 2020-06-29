<?php

declare(strict_types=1);

/*
 * @author    mfris
 * @copyright PIXELFEDERATION s.r.o.
 * @license   Internal use only
 */

namespace PixelFederation\CodingStandards\Example\Common;

interface EntityRepository
{
    public function findOneBy(array $array): ?object;
}
