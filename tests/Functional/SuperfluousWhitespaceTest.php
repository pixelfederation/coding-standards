<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

final class SuperfluousWhitespaceTest extends PhpcsTestCase
{
    public function testPhpcbfFixesWhitespaceAsExpected(): void
    {
        self::assertPhpcbf(
            'SuperfluousWhitespace/Bar.SuperfluousWhitespace.before.php',
            'SuperfluousWhitespace/Bar.SuperfluousWhitespace.after.php',
        );
    }
}
