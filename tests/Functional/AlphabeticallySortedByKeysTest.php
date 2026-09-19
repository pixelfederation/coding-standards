<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use RuntimeException;

final class AlphabeticallySortedByKeysTest extends PhpcsTestCase
{
    public function testConfiguredParentKeysAreIgnoredWithoutIgnoringContainingArrays(): void
    {
        $messages = $this->runSniff('AlphabeticallySortedByKeys/Arrays.php', ['choices']);

        self::assertSame([13, 23, 34], array_column($messages, 'line'));
        self::assertSame(
            [
                'PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder',
                'PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder',
                'PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder',
            ],
            array_column($messages, 'source'),
        );
    }

    public function testEmptyIgnoredParentKeysPreservesOriginalBehaviour(): void
    {
        $messages = $this->runSniff('AlphabeticallySortedByKeys/EmptyConfiguration.php', []);

        self::assertCount(1, $messages);
        self::assertSame(6, $messages[0]['line'] ?? null);
        self::assertSame(
            'PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder',
            $messages[0]['source'] ?? null,
        );
    }

    public function testPackagedRulesetConfiguresIgnoredParentKeys(): void
    {
        $messages = $this->runSniffWithRuleset(
            __DIR__ . '/AlphabeticallySortedByKeys/Arrays.php',
            self::getPath('PATH_PHPCS_RULESET'),
            1,
        );

        self::assertSame([13, 23, 34], array_column($messages, 'line'));
    }

    public function testMatchArmArraysAreNotIgnoredEvenWhenArmConditionMatchesIgnoredKey(): void
    {
        $messages = $this->runSniff('AlphabeticallySortedByKeys/MatchArms.php', ['choices']);

        self::assertSame([8, 12], array_column($messages, 'line'));
        self::assertSame(
            [
                'PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder',
                'PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys.IncorrectKeyOrder',
            ],
            array_column($messages, 'source'),
        );
    }

    public function testLongArraySyntaxIsIgnored(): void
    {
        $temporaryPath = tempnam(sys_get_temp_dir(), 'phpcs-array-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Could not create temporary PHP file.');
        }

        $path = $temporaryPath . '.php';
        $content = $this->getEscapedKeysSnippet();

        try {
            if (!rename($temporaryPath, $path) || file_put_contents($path, $content) === false) {
                throw new RuntimeException('Could not create temporary PHP file.');
            }

            self::assertSame([], $this->runSniffOnPath(
                $path,
                ['choices', 'choices\q', 'choices\X41', 'choi"ces', "choi\\'ces", "control\u{80}"],
                0,
            ));
        } finally {
            if (is_file($temporaryPath)) {
                unlink($temporaryPath);
            }
            if (is_file($path)) {
                unlink($path);
            }
        }
    }

    private function getEscapedKeysSnippet(): string
    {
        return $this->getBasicEscapesSnippet() . $this->getAdditionalEscapesSnippet();
    }

    private function getBasicEscapesSnippet(): string
    {
        return <<<'PHP'
            <?php

            $config = array(
                "choices" => array(
                    'zebra' => true,
                    'alpha' => true,
                ),
            );
            $unknownEscape = [
                "choices\q" => [
                    'zebra' => true,
                    'alpha' => true,
                ],
            ];
            $uppercaseHexEscape = [
                "choices\X41" => [
                    'zebra' => true,
                    'alpha' => true,
                ],
            ];
            $escapedQuote = [
                "choi\"ces" => [
                    'zebra' => true,
                    'alpha' => true,
                ],
            ];
            PHP;
    }

    private function getAdditionalEscapesSnippet(): string
    {
        return <<<'PHP'

            $unicodeEscape = [
                "cho\u{69}ces" => [
                    'zebra' => true,
                    'alpha' => true,
                ],
            ];
            $unicodeControlEscape = [
                "control\u{80}" => [
                    'zebra' => true,
                    'alpha' => true,
                ],
            ];
            $singleQuotedEscapes = [
                'choi\\\'ces' => [
                    'zebra' => true,
                    'alpha' => true,
                ],
            ];
            PHP;
    }

    /**
     * @param list<string> $ignoredParentKeys
     * @return list<array<mixed, mixed>>
     */
    private function runSniff(string $fixture, array $ignoredParentKeys): array
    {
        return $this->runSniffOnPath(__DIR__ . '/' . $fixture, $ignoredParentKeys, 1);
    }

    /**
     * @param list<string> $ignoredParentKeys
     * @return list<array<mixed, mixed>>
     */
    private function runSniffOnPath(string $path, array $ignoredParentKeys, int $expectedExitCode): array
    {
        $ruleset = $this->createRuleset($ignoredParentKeys);

        try {
            return $this->runSniffWithRuleset($path, $ruleset, $expectedExitCode);
        } finally {
            unlink($ruleset);
        }
    }

    /** @return list<array<mixed, mixed>> */
    private function runSniffWithRuleset(string $path, string $ruleset, int $expectedExitCode): array
    {
        $command = sprintf(
            '%s -q --standard=%s --sniffs=%s --report=json %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCS')),
            escapeshellarg($ruleset),
            escapeshellarg('PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys'),
            escapeshellarg($path),
        );

        exec($command, $output, $exitCode);
        self::assertSame($expectedExitCode, $exitCode, implode("\n", $output));

        return $this->extractMessages(implode("\n", $output));
    }

    /** @return list<array<mixed, mixed>> */
    private function extractMessages(string $json): array
    {
        $report = json_decode($json, true);
        if (!is_array($report)) {
            throw new RuntimeException('Could not decode PHPCS JSON report.');
        }

        $files = $report['files'] ?? null;
        if (!is_array($files)) {
            throw new RuntimeException('PHPCS JSON report does not contain files.');
        }

        $file = array_values($files)[0] ?? null;
        if (!is_array($file) || !is_array($file['messages'] ?? null)) {
            throw new RuntimeException('PHPCS JSON report does not contain messages.');
        }

        return array_map($this->requireMessage(...), array_values($file['messages']));
    }

    /** @return array<mixed, mixed> */
    private function requireMessage(mixed $message): array
    {
        if (!is_array($message)) {
            throw new RuntimeException('PHPCS JSON report contains an invalid message.');
        }

        return $message;
    }

    /** @param list<string> $ignoredParentKeys */
    private function createRuleset(array $ignoredParentKeys): string
    {
        $elements = implode('', array_map(
            static fn (string $key): string => sprintf(
                '<element value="%s"/>',
                htmlspecialchars($key, ENT_QUOTES | ENT_XML1),
            ),
            $ignoredParentKeys,
        ));
        $content = sprintf(
            '<?xml version="1.0"?><ruleset name="test"><config name="installed_paths" value="%s,%s"/>'
            . '<rule ref="PixelFederationCodingStandard.Arrays.AlphabeticallySortedByKeys"><properties>'
            . '<property name="ignoredParentKeys" type="array">%s</property></properties></rule></ruleset>',
            __DIR__ . '/../../src/PixelFederationCodingStandard',
            __DIR__ . '/../../vendor/slevomat/coding-standard',
            $elements,
        );
        $temporaryPath = tempnam(sys_get_temp_dir(), 'phpcs-ruleset-');
        if ($temporaryPath === false) {
            throw new RuntimeException('Could not create temporary PHPCS ruleset.');
        }

        $path = $temporaryPath . '.xml';
        if (!rename($temporaryPath, $path) || file_put_contents($path, $content) === false) {
            throw new RuntimeException('Could not create temporary PHPCS ruleset.');
        }

        return $path;
    }
}
