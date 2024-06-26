<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Php74;

use PixelFederation\CodingStandards\Example\Php74\Common\EntityManager;
use PixelFederation\CodingStandards\Example\Php74\Common\HardwareId;

final class HardwareRepository
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function findUser(HardwareId $id): ?Hardware
    {
        return $this->fetchDataFromDatabase($id);
    }

    private function fetchDataFromDatabase(HardwareId $id): ?Hardware
    {
        $repository = $this->entityManager->getRepository(HardwareEntity::class);

        return $repository->findOneBy(['id' => $id]);
    }
}
