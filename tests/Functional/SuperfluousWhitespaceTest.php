<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

final class SuperfluousWhitespaceTest extends PhpcsTestCase
{
    public function testPhpcbfFixesWhitespaceAsExpected(): void
    {
        $fileBefore = 'SuperfluousWhitespace/Bar.SuperfluousWhitespace.before.php';
        $fileAfter = 'SuperfluousWhitespace/Bar.SuperfluousWhitespace.after.php';

        // Ensure no trailing newlines interfere with the comparison
        self::trimEndFileNewline($fileBefore);

        self::assertPhpcbf($fileBefore, $fileAfter);
    }
}
