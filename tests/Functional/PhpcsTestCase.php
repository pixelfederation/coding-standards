<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use PHPUnit\Framework\TestCase;
use RuntimeException;

abstract class PhpcsTestCase extends TestCase
{
    public static function assertPhpcbf(
        string $fileBefore,
        string $fileAfter,
        string $message = '',
        string $tmpFilename = 'Bar.php',
    ): void {
        $before = __DIR__ . '/' . $fileBefore;
        $expected = __DIR__ . '/' . $fileAfter;

        self::assertFileExists($before, 'Before file does not exist: ' . $before);
        self::assertFileExists($expected, 'After file does not exist: ' . $expected);

        $tmpDir = sys_get_temp_dir() . '/cs_bar_' . bin2hex(random_bytes(4));
        self::assertTrue(mkdir($tmpDir, 0777, true), $message);

        $tmpFile = $tmpDir . '/' . $tmpFilename;
        $beforeContent = file_get_contents($before);
        if ($beforeContent === false) {
            throw new RuntimeException('Could not read file: ' . $before);
        }

        try {
            self::assertNotFalse(file_put_contents($tmpFile, $beforeContent), $message);

            [$exitCodeCbf, $outputCbf] = self::runPhpcsCommand('PATH_PHPCBF', $tmpFile);
            self::assertSame(0, $exitCodeCbf, implode("\n", $outputCbf) . $message);

            self::assertFixedContent($tmpFile, $expected, $beforeContent, $message);

            [$exitCodeCs, $outputCs] = self::runPhpcsCommand('PATH_PHPCS', $tmpFile);
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
        } finally {
            self::removeTemporaryFiles($tmpFile, $tmpDir);
        }
    }

    protected static function getPath(string $env): string
    {
        return __DIR__ . '/../../' . getenv($env);
    }

    private static function assertFixedContent(
        string $actualFile,
        string $expectedFile,
        string $beforeContent,
        string $message,
    ): void {
        $actualContent = file_get_contents($actualFile);
        self::assertNotSame(
            $beforeContent,
            $actualContent,
            'Expected PHPCBF to change the file, but contents are identical.' . $message,
        );
        self::assertSame(
            file_get_contents($expectedFile),
            $actualContent,
            "PHPCBF output does not match expected 'after' file. " . $message,
        );
    }

    /**
     * @return array{int, list<string>}
     */
    private static function runPhpcsCommand(string $binaryPathEnv, string $file): array
    {
        $command = sprintf(
            '%s --standard=%s %s 2>&1',
            escapeshellarg(self::getPath($binaryPathEnv)),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg($file),
        );

        $output = [];
        exec($command, $output, $exitCode);

        return [$exitCode, $output];
    }

    private static function removeTemporaryFiles(string $file, string $directory): void
    {
        if (is_file($file)) {
            unlink($file);
        }

        if (!is_dir($directory)) {
            return;
        }

        rmdir($directory);
    }
}
