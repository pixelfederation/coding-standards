<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\Tests\Functional\ReferenceUsedNamesOnly;

use ArrayObject;
use LogicException;

use function strlen;

use const PHP_EOL;

/**
 * Demonstrates every reference style checked by
 * SlevomatCodingStandard.Namespaces.ReferenceUsedNamesOnly, with the project's current
 * ruleset settings:
 *   - allowFullyQualifiedExceptions      = false
 *   - allowFullyQualifiedGlobalClasses   = false
 *   - allowFullyQualifiedGlobalFunctions = false
 *   - allowFullyQualifiedGlobalConstants = false
 *   - allowFallbackGlobalFunctions       = true
 *   - allowFallbackGlobalConstants       = true
 */
final class Usages
{
    public function exceptionImportedIsOk(): void
    {
        // OK: exception imported via `use` and referenced by its short name.
        throw new LogicException('imported exception');
    }

    public function exceptionFullyQualifiedIsNotOk(): void
    {
        // NOT OK: allowFullyQualifiedExceptions=false, so a fully qualified exception
        // reference (class name ends with "Exception") must be imported instead.
        throw new \RuntimeException('fully qualified exception');
    }

    public function errorSuffixedFullyQualifiedIsNotOk(): void
    {
        // NOT OK: a fully qualified, non-namespaced class ending in "Error" is treated
        // as an exception too, so it must be imported instead.
        throw new \TypeError('fully qualified error');
    }

    public function globalClassImportedIsOk(): void
    {
        // OK: global (non-exception) class imported via `use` and referenced by its
        // short name.
        new ArrayObject();
    }

    public function globalClassFullyQualifiedIsNotOk(): void
    {
        // NOT OK: allowFullyQualifiedGlobalClasses=false, so a fully qualified global
        // class reference must be imported instead, even for native PHP classes.
        new \DateTimeImmutable();
    }

    public function globalFunctionImportedIsOk(): void
    {
        // OK: function imported via `use function` and referenced by its short name.
        echo strlen('imported function');
    }

    public function globalFunctionFallbackIsOk(): void
    {
        // OK: allowFallbackGlobalFunctions=true, so an unqualified call that falls back
        // to the global namespace (no import, no leading backslash) is allowed.
        echo trim('fallback function');
    }

    public function globalFunctionFullyQualifiedIsNotOk(): void
    {
        // NOT OK: allowFullyQualifiedGlobalFunctions=false, so a fully qualified
        // function call must be imported (or written unqualified) instead.
        echo \strtoupper('fully qualified function');
    }

    public function globalConstantImportedIsOk(): void
    {
        // OK: constant imported via `use const` and referenced by its short name.
        echo PHP_EOL;
    }

    public function globalConstantFallbackIsOk(): void
    {
        // OK: allowFallbackGlobalConstants=true, so an unqualified constant that falls
        // back to the global namespace (no import, no leading backslash) is allowed.
        echo PHP_INT_MAX;
    }

    public function globalConstantFullyQualifiedIsNotOk(): void
    {
        // NOT OK: allowFullyQualifiedGlobalConstants=false, so a fully qualified
        // constant must be imported (or written unqualified) instead.
        echo \PHP_VERSION;
    }
}
