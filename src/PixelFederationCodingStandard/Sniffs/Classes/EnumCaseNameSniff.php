<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\PixelFederationCodingStandard\Sniffs\Classes;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Common;
use PHP_CodeSniffer\Util\Tokens;

final class EnumCaseNameSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(): array
    {
        return [
            T_ENUM_CASE,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        /** @var array<int, array{code: int|string, content: string}> $tokens */
        $tokens = $phpcsFile->getTokens();

        $namePointer = $phpcsFile->findNext(Tokens::EMPTY_TOKENS, $stackPtr + 1, null, true);
        if ($namePointer === false || $tokens[$namePointer]['code'] !== T_STRING) {
            return;
        }

        $name = $tokens[$namePointer]['content'];

        if (Common::isCamelCaps($name, true, true, true) === true) {
            return;
        }

        $phpcsFile->addError(
            'Enum case name "%s" is not in PascalCase format',
            $namePointer,
            'NotPascalCase',
            [$name],
        );
    }
}
