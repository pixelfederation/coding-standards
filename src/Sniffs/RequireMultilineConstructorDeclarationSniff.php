<?php

declare(strict_types = 1);

namespace PixelFederation\CodingStandards\Sniffs;

use PHP_CodeSniffer\Files\File;
use SlevomatCodingStandard\Helpers\IndentationHelper;
use SlevomatCodingStandard\Helpers\SniffSettingsHelper;
use SlevomatCodingStandard\Helpers\TokenHelper;
use SlevomatCodingStandard\Sniffs\Classes\AbstractMethodSignature;
use function count;
use function strtolower;
use const T_COMMA;
use const T_FUNCTION;
use const T_WHITESPACE;

class RequireMultilineConstructorDeclarationSniff extends AbstractMethodSignature
{
    public const CODE_REQUIRED_MULTI_LINE_CONSTRUCTOR_DECLARATION = 'RequiredMultilineConstructorDeclaration';

    /** @var bool|null */
    public $enable = null;

    /**
     * @return array<int, (int|string)>
     */
    public function register(): array
    {
        return [T_FUNCTION];
    }

    /**
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ParameterTypeHint.MissingNativeTypeHint
     * @param File $phpcsFile
     * @param int $functionPointer
     */
    public function process(File $phpcsFile, $stackPtr): void
    {
        $this->enable = SniffSettingsHelper::isEnabledByPhpVersion($this->enable, 80000);

        if (!$this->enable) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $namePointer = TokenHelper::findNextEffective($phpcsFile, $stackPtr + 1);

        if (strtolower($tokens[$namePointer]['content']) !== '__construct') {
            return;
        }

        [$signatureStartPointer, $signatureEndPointer] = $this->getSignatureStartAndEndPointers($phpcsFile, $stackPtr);

        if ($tokens[$signatureStartPointer]['line'] < $tokens[$signatureEndPointer]['line']) {
            return;
        }

        $error = 'Signature of __construct should be split to more lines so each parameter is on its own line.';
        $fix = $phpcsFile->addFixableError($error, $stackPtr, self::CODE_REQUIRED_MULTI_LINE_CONSTRUCTOR_DECLARATION);
        if (!$fix) {
            return;
        }

        $parameters = $phpcsFile->getMethodParameters($stackPtr);
        if (count($parameters) === 0) {
            return;
        }

        $indentation = $tokens[$signatureStartPointer]['content'];

        $phpcsFile->fixer->beginChangeset();

        foreach ($parameters as $parameter) {
            $pointerBeforeParameter = TokenHelper::findPrevious(
                $phpcsFile,
                T_COMMA,
                $parameter['token'] - 1,
                $tokens[$stackPtr]['parenthesis_opener']
            );
            if ($pointerBeforeParameter === null) {
                $pointerBeforeParameter = $tokens[$stackPtr]['parenthesis_opener'];
            }

            $phpcsFile->fixer->addContent($pointerBeforeParameter, $phpcsFile->eolChar . IndentationHelper::addIndentation($indentation));
            for ($i = $pointerBeforeParameter + 1; $i < $parameter['token']; $i++) {
                if ($tokens[$i]['code'] !== T_WHITESPACE) {
                    break;
                }

                $phpcsFile->fixer->replaceToken($i, '');
            }
        }

        $phpcsFile->fixer->addContentBefore($tokens[$stackPtr]['parenthesis_closer'], $phpcsFile->eolChar . $indentation);

        $phpcsFile->fixer->endChangeset();
    }
}
