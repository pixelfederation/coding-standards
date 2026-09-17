<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

final class ForbiddenAnnotationsTest extends PhpcsTestCase
{
    public function testOnlyConfiguredAnnotationsAreForbidden(): void
    {
        $command = sprintf(
            '%s -q -s --standard=%s --sniffs=%s %s 2>&1',
            escapeshellarg(self::getPath('PATH_PHPCS')),
            escapeshellarg(self::getPath('PATH_PHPCS_RULESET')),
            escapeshellarg('SlevomatCodingStandard.Commenting.ForbiddenAnnotations'),
            escapeshellarg(__DIR__ . '/ForbiddenAnnotations/Annotations.php'),
        );

        exec($command, $output, $exitCode);
        $report = implode("\n", $output);

        self::assertSame(1, $exitCode, $report);
        self::assertStringContainsString('Use of annotation @author is forbidden.', $report);
        self::assertStringContainsString('Use of annotation @copyright is forbidden.', $report);
        self::assertStringContainsString('Use of annotation @license is forbidden.', $report);
        self::assertStringNotContainsString('Use of annotation @throws is forbidden.', $report);
        self::assertSame(3, substr_count($report, 'AnnotationForbidden'));
    }
}
