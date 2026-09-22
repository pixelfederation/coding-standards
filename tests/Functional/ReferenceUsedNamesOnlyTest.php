<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use RuntimeException;

final class ReferenceUsedNamesOnlyTest extends PhpcsTestCase
{
    public function testAllReferenceStylesForExceptionsClassesFunctionsAndConstants(): void
    {
        $command = sprintf(
            '%s -q --standard=%s --sniffs=%s --report=json %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCS')),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg('SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly'),
            escapeshellarg(__DIR__ . '/ReferenceUsedNamesOnly/Usages.php'),
        );

        exec($command, $output, $exitCode);

        self::assertSame(1, $exitCode, implode("\n", $output));

        $report = self::requireArray(json_decode(implode("\n", $output), true), 'Could not decode PHPCS JSON report.');
        $totals = self::requireArray($report['totals'] ?? null, 'PHPCS JSON report does not contain totals.');

        // OK (no error): exceptionImportedIsOk, globalClassImportedIsOk,
        // globalFunctionImportedIsOk, globalFunctionFallbackIsOk, globalConstantImportedIsOk,
        // globalConstantFallbackIsOk.
        // NOT OK (error): exceptionFullyQualifiedIsNotOk, errorSuffixedFullyQualifiedIsNotOk,
        // globalClassFullyQualifiedIsNotOk, globalFunctionFullyQualifiedIsNotOk,
        // globalConstantFullyQualifiedIsNotOk.
        self::assertSame(5, $totals['errors'] ?? null);
        self::assertSame(0, $totals['warnings'] ?? null);

        $reportFiles = self::requireArray($report['files'] ?? null, 'PHPCS JSON report does not contain files.');
        $files = array_values($reportFiles);
        self::assertCount(1, $files);

        $file = self::requireArray($files[0] ?? null, 'PHPCS JSON report contains an invalid file result.');
        $messages = self::requireArray($file['messages'] ?? null, 'PHPCS JSON file result does not contain messages.');
        self::assertCount(5, $messages);

        $expectedViolations = [
            37 => ['Class', '\RuntimeException'],
            44 => ['Class', '\TypeError'],
            58 => ['Class', '\DateTimeImmutable'],
            78 => ['Function', '\strtoupper()'],
            98 => ['Constant', '\PHP_VERSION'],
        ];

        foreach ($messages as $index => $message) {
            $message = self::requireArray($message, 'PHPCS JSON report contains an invalid message.');
            $line = $message['line'] ?? null;

            self::assertIsInt($line, sprintf('Message #%d has no line number.', $index));
            self::assertArrayHasKey($line, $expectedViolations, sprintf('Unexpected violation on line %d.', $line));
            self::assertSame(
                'SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly.ReferenceViaFullyQualifiedName',
                $message['source'] ?? null,
            );

            [$label, $name] = $expectedViolations[$line];
            $expectedMessage = sprintf(
                '%s %s should not be referenced via a fully qualified name, but via a use statement.',
                $label,
                $name,
            );
            self::assertSame($expectedMessage, $message['message'] ?? null);
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
