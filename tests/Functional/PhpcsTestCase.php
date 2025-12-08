<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use PHPUnit\Framework\TestCase;

abstract class PhpcsTestCase extends TestCase
{
    public static function assertPhpcbf(string $fileBefore, string $fileAfter, string $message = ''): void
    {
        $before = __DIR__ . '/' . $fileBefore;
        $expected = __DIR__ . '/' . $fileAfter;

        self::assertFileExists($before, 'Before file does not exist: ' . $before);
        self::assertFileExists($expected, 'After file does not exist: ' . $expected);

        $tmpDir = sys_get_temp_dir() . '/cs_bar_' . bin2hex(random_bytes(4));
        self::assertTrue(mkdir($tmpDir, 0777, true), $message);

        $tmpFile = $tmpDir . '/Bar.php';
        self::assertNotFalse($tmpFile, $message);
        file_put_contents($tmpFile, file_get_contents($before));

        $cmdCbf = sprintf(
            '%s --standard=%s %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCBF')),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg($tmpFile),
        );

        $beforeContent = file_get_contents($before);
        file_put_contents($tmpFile, $beforeContent);

        exec($cmdCbf, $outputCbf, $exitCodeCbf);
        self::assertSame(0, $exitCodeCbf, $message);

        $actual = file_get_contents($tmpFile);
        self::assertNotSame(
            $beforeContent,
            $actual,
            'Expected PHPCBF to change the file, but contents are identical.' . $message,
        );

        $actual = file_get_contents($tmpFile);
        self::assertSame(
            file_get_contents($expected),
            $actual,
            "PHPCBF output does not match expected 'after' file. " . $message,
        );

        $cmdCs = sprintf(
            '%s --standard=%s %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCS')),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg($tmpFile),
        );

        exec($cmdCs, $outputCs, $exitCodeCs);

        self::assertSame(
            0,
            $exitCodeCs,
            sprintf(
                "Expected no PHPCS errors after PHPCBF fix, got exit code %s.\nOutput:\n%s %s",
                $exitCodeCs,
                implode("\n", $outputCs),
                $message,
            ),
        );

        @unlink($tmpFile);
    }

    protected static function getPath(string $env): string
    {
        return __DIR__ . '/../../' . getenv($env);
    }
}
