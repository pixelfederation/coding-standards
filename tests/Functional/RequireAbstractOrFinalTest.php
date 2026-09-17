<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use RuntimeException;

final class RequireAbstractOrFinalTest extends PhpcsTestCase
{
    public function testFinalAnnotationIsAccepted(): void
    {
        $command = sprintf(
            '%s -q --standard=%s --sniffs=%s --report=json %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCS')),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg('PixelFederationCodingStandard.Classes.RequireAbstractOrFinal'),
            escapeshellarg(__DIR__ . '/RequireAbstractOrFinal/Classes.php'),
        );

        exec($command, $output, $exitCode);

        self::assertSame(1, $exitCode, implode("\n", $output));

        $report = self::requireArray(json_decode(implode("\n", $output), true), 'Could not decode PHPCS JSON report.');
        $totals = self::requireArray($report['totals'] ?? null, 'PHPCS JSON report does not contain totals.');

        self::assertSame(1, $totals['errors'] ?? null);
        self::assertSame(0, $totals['warnings'] ?? null);

        $reportFiles = self::requireArray($report['files'] ?? null, 'PHPCS JSON report does not contain files.');
        $files = array_values($reportFiles);
        self::assertCount(1, $files);

        $file = self::requireArray($files[0] ?? null, 'PHPCS JSON report contains an invalid file result.');
        $messages = self::requireArray($file['messages'] ?? null, 'PHPCS JSON file result does not contain messages.');
        self::assertCount(1, $messages);

        $message = self::requireArray($messages[0] ?? null, 'PHPCS JSON report contains an invalid message.');
        self::assertSame(21, $message['line'] ?? null);
        self::assertSame(
            'PixelFederationCodingStandard.Classes.RequireAbstractOrFinal.ClassNeitherAbstractNorFinal',
            $message['source'] ?? null,
        );
    }

    /**
     * @return array<array-key, mixed>
     */
    private static function requireArray(mixed $value, string $errorMessage): array
    {
        if (!is_array($value)) {
            throw new RuntimeException($errorMessage);
        }

        return $value;
    }
}
