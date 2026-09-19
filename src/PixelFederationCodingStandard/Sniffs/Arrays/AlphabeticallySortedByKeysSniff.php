<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\PixelFederationCodingStandard\Sniffs\Arrays;

use Override;
use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Sniffs\Arrays\AlphabeticallySortedByKeysSniff as SlevomatAlphabeticallySortedByKeysSniff;

use function in_array;
use function str_replace;
use function stripcslashes;
use function strlen;
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
        if (strlen($literal) < 2) {
            return $literal;
        }

        $quote = $literal[0];
        $content = substr($literal, 1, -1);

        if ($quote === "'") {
            return str_replace(['\\\\', '\\\''], ['\\', '\''], $content);
        }

        return stripcslashes($content);
    }
}
