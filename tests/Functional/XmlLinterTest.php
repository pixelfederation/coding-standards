<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional;

use PixelFederation\CodingStandards\GrumPHP\Linter\Xml\XmlLinter;
use RuntimeException;
use SplFileInfo;

final class XmlLinterTest extends PhpcsTestCase
{
    public function testValidDocumentWithMultipleNamespacesPassesSchemaValidation(): void
    {
        $linter = new XmlLinter();
        $linter->setLoadFromNet(false);
        $linter->setXInclude(false);
        $linter->setDtdValidation(false);
        $linter->setSchemeValidation(true);

        $errors = $linter->lint(new SplFileInfo(__DIR__ . '/XmlLinter/valid-multiple-namespaces.xml'));

        self::assertCount(0, $errors, (string) $errors);
    }

    public function testInternalErrorSettingIsRestoredWhenLoadingThrows(): void
    {
        $previousUseInternalErrors = libxml_use_internal_errors(false);
        libxml_set_external_entity_loader(
            static fn (): never => throw new RuntimeException('Expected external entity loader failure.'),
        );

        try {
            $linter = new XmlLinter();
            $linter->setLoadFromNet(true);

            self::expectException(RuntimeException::class);

            try {
                $linter->lint(new SplFileInfo(__DIR__ . '/XmlLinter/external-dtd.xml'));
            } finally {
                self::assertFalse(libxml_use_internal_errors());
            }
        } finally {
            libxml_set_external_entity_loader(null);
            libxml_use_internal_errors($previousUseInternalErrors);
            libxml_clear_errors();
        }
    }
}
