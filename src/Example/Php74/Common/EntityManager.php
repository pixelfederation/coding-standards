<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Php74\Common;

interface EntityManager
{
    public function getRepository(string $entityClass): EntityRepository;
}
