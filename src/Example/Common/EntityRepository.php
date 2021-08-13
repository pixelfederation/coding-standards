<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Common;

interface EntityRepository
{
    public function findOneBy(array $array): ?object;
}
