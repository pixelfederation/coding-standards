<?php

declare(strict_types=1);

/*
 * @author    mfris
 * @copyright PIXELFEDERATION s.r.o.
 * @license   Internal use only
 */

namespace PixelFederation\CodingStandards\Example\Common;

interface EntityManager
{
    public function getRepository(string $entityClass): EntityRepository;
}
