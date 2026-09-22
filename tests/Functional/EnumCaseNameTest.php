<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use RuntimeException;

final class EnumCaseNameTest extends PhpcsTestCase
{
    public function testEnumCaseNamesMustBePascalCase(): void
    {
        $command = sprintf(
            '%s -q --standard=%s --sniffs=%s --report=json %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCS')),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg('PixelFederationCodingStandard.Classes.EnumCaseName'),
            escapeshellarg(__DIR__ . '/EnumCaseName/Enums.php'),
        );

        exec($command, $output, $exitCode);

        self::assertSame(2, $exitCode, implode("\n", $output));

        $report = self::requireArray(json_decode(implode("\n", $output), true), 'Could not decode PHPCS JSON report.');
        $totals = self::requireArray($report['totals'] ?? null, 'PHPCS JSON report does not contain totals.');

        // Valid: One, Two, ThreeFour.
        // Invalid: ONE (all caps), This_Code (underscore), ThisCODE (trailing acronym),
        // thisCase (starts lowercase), AB (two-letter acronym).
        self::assertSame(5, $totals['errors'] ?? null);
        self::assertSame(0, $totals['warnings'] ?? null);

        $reportFiles = self::requireArray($report['files'] ?? null, 'PHPCS JSON report does not contain files.');
        $files = array_values($reportFiles);
        self::assertCount(1, $files);

        $file = self::requireArray($files[0] ?? null, 'PHPCS JSON report contains an invalid file result.');
        $messages = self::requireArray($file['messages'] ?? null, 'PHPCS JSON file result does not contain messages.');
        self::assertCount(5, $messages);

        $expectedViolations = [
            10 => 'ONE',
            11 => 'This_Code',
            12 => 'ThisCODE',
            13 => 'thisCase',
            14 => 'AB',
        ];

        foreach ($messages as $index => $message) {
            $message = self::requireArray($message, 'PHPCS JSON report contains an invalid message.');
            $line = $message['line'] ?? null;

            self::assertIsInt($line, sprintf('Message #%d has no line number.', $index));
            self::assertArrayHasKey($line, $expectedViolations, sprintf('Unexpected violation on line %d.', $line));
            self::assertSame(
                'PixelFederationCodingStandard.Classes.EnumCaseName.NotPascalCase',
                $message['source'] ?? null,
            );
            self::assertSame(
                sprintf('Enum case name "%s" is not in PascalCase format', $expectedViolations[$line]),
                $message['message'] ?? null,
            );
        }
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
