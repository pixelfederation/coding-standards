<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\PixelFederationCodingStandard\Sniffs\Classes;

use Override;
use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\AnnotationHelper;
use SlevomatCodingStandard\Sniffs\Classes\RequireAbstractOrFinalSniff as SlevomatRequireAbstractOrFinalSniff;

final class RequireAbstractOrFinalSniff extends SlevomatRequireAbstractOrFinalSniff
{
    #[Override]
    public function process(File $phpcsFile, int $classPointer): void
    {
        if (AnnotationHelper::getAnnotations($phpcsFile, $classPointer, '@final') !== []) {
            return;
        }

        parent::process($phpcsFile, $classPointer);
    }
}
