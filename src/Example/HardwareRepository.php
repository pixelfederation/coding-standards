<?php

declare(strict_types=1);

/*
 * @author    mfris
 * @copyright PIXELFEDERATION s.r.o.
 * @license   Internal use only
 */

namespace PixelFederation\CodingStandards\Example;

use PixelFederation\CodingStandards\Example\Common\EntityManager;
use PixelFederation\CodingStandards\Example\Common\HardwareId;

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
