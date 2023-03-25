<?php

/**
 * @author Martin Fris <mfris@pixelfederation.com>
 */

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Example\Php74;

use PixelFederation\CodingStandards\Example\Php74\Common\HardwareId;
use PixelFederation\CodingStandards\Example\Php74\Common\SerialNumber;
use PixelFederation\CodingStandards\Example\Php74\Common\ServiceTag;

final class HardwareEntity implements Hardware
{
    private HardwareId $id;
    private SerialNumber $serialNumber;
    private ServiceTag $serviceTag;

    public function __construct(HardwareId $id, SerialNumber $serialNumber, ServiceTag $serviceTag)
    {
        $this->id = $id;
        $this->serialNumber = $serialNumber;
        $this->serviceTag = $serviceTag;
    }

    public function getId(): HardwareId
    {
        return $this->id;
    }

    public function getSerialNumber(): SerialNumber
    {
        return $this->serialNumber;
    }

    public function getServiceTag(): ServiceTag
    {
        return $this->serviceTag;
    }
}
