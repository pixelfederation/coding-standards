<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP;

use GrumPHP\Extension\ExtensionInterface;
use Override;

final class ExtensionLoader implements ExtensionInterface
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function imports(): iterable
    {
        $configDir = dirname(__DIR__) . '/../config';

        yield $configDir . '/grumphp.yaml';
    }
}
