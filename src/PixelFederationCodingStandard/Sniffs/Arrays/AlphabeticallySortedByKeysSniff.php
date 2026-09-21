<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\PixelFederationCodingStandard\Sniffs\Arrays;

use Override;
use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Sniffs\Arrays\AlphabeticallySortedByKeysSniff as SlevomatAlphabeticallySortedByKeysSniff;

use function hexdec;
use function in_array;
use function is_string;
use function json_decode;
use function preg_replace_callback;
use function sprintf;
use function str_starts_with;
use function stripcslashes;
use function substr;

use const T_CONSTANT_ENCAPSED_STRING;
use const T_DOUBLE_ARROW;

final class AlphabeticallySortedByKeysSniff extends SlevomatAlphabeticallySortedByKeysSniff
{
    /**
     * @var list<string>
     */
    public array $ignoredParentKeys = [];

    #[Override]
    public function process(File $phpcsFile, int $stackPointer): void
    {
        if ($this->isValueOfIgnoredParentKey($phpcsFile, $stackPointer)) {
            return;
        }

        parent::process($phpcsFile, $stackPointer);
    }

    private function isValueOfIgnoredParentKey(File $phpcsFile, int $stackPointer): bool
    {
        if ($this->ignoredParentKeys === []) {
            return false;
        }

        /** @var array<int, array{code: int|string, content: string}> $tokens */
        $tokens = $phpcsFile->getTokens();

        $doubleArrowPointer = TokenHelper::findPreviousEffective($phpcsFile, $stackPointer - 1);
        if ($doubleArrowPointer === null || $tokens[$doubleArrowPointer]['code'] !== T_DOUBLE_ARROW) {
            return false;
        }

        $keyPointer = TokenHelper::findPreviousEffective($phpcsFile, $doubleArrowPointer - 1);
        if ($keyPointer === null || $tokens[$keyPointer]['code'] !== T_CONSTANT_ENCAPSED_STRING) {
            return false;
        }

        $key = $this->decodeStringLiteral($tokens[$keyPointer]['content']);

        return in_array($key, $this->ignoredParentKeys, true);
    }

    private function decodeStringLiteral(string $literal): string
    {
        $content = substr($literal, 1, -1);
        if ($literal[0] === "'") {
            return preg_replace_callback(
                '~\\\\([\\\\\'])~',
                static fn (array $matches): string => $matches[1],
                $content,
            ) ?? $content;
        }

        return preg_replace_callback(
            '~\\\\(?:[nrtvef\\\\$"]|[0-7]{1,3}|x[0-9A-Fa-f]{1,2}|u\{[0-9A-Fa-f]+\})~',
            function (array $matches): string {
                if (str_starts_with($matches[0], '\\u{')) {
                    return $this->decodeUnicodeEscape($matches[0]);
                }

                return stripcslashes($matches[0]);
            },
            $content,
        ) ?? $content;
    }

    private function decodeUnicodeEscape(string $escape): string
    {
        $codePoint = (int) hexdec(substr($escape, 3, -1));
        if ($codePoint < 0x10000) {
            return $this->decodeJsonString(sprintf('"\\u%04x"', $codePoint), $escape);
        }

        $codePoint -= 0x10000;
        $json = sprintf('"\\u%04x\\u%04x"', 0xd800 + ($codePoint >> 10), 0xdc00 + ($codePoint & 0x3ff));

        return $this->decodeJsonString($json, $escape);
    }

    private function decodeJsonString(string $json, string $fallback): string
    {
        $decoded = json_decode($json);

        return is_string($decoded) ? $decoded : $fallback;
    }
}
