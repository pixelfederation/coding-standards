<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\PixelFederationCodingStandard\Sniffs\ControlStructures;

use Override;
use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

final class DisallowSwitchStatementSniff implements Sniff
{
    /**
     * @inheritDoc
     */
    #[Override]
    public function register(): array
    {
        return [
            T_SWITCH,
        ];
    }

    /**
     * @inheritDoc
     */
    #[Override]
    public function process(File $phpcsFile, $stackPtr): void
    {
        $phpcsFile->addError('Use of switch() is disallowed.', $stackPtr, 'DisallowedSwitch');
    }
}
