<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Linter\Xml;

use DOMDocument;
use GrumPHP\Collection\LintErrorsCollection;
use GrumPHP\Linter\LinterInterface;
use GrumPHP\Linter\LintError;
use GrumPHP\Linter\Xml\XmlLintError;
use LibXMLError;
use Override;
use SplFileInfo;

/**
 * @see \GrumPHP\Linter\Xml\XmlLinter
 */
final class XmlLinter implements LinterInterface
{
    private const string XSI_NAMESPACE = 'http://www.w3.org/2001/XMLSchema-instance';

    private bool $loadFromNet = true;
    private bool $xInclude = true;
    private bool $dtdValidation = true;
    private bool $schemeValidation = true;

    #[Override]
    public function lint(SplFileInfo $file): LintErrorsCollection
    {
        $errors = new LintErrorsCollection();
        $useInternalErrors = $this->useInternalXmlLogging(true);
        $this->flushXmlErrors();

        $document = $this->loadDocument($file);
        if (!$document) {
            $this->collectXmlErrors($errors, null);
            $this->useInternalXmlLogging($useInternalErrors);

            return $errors;
        }

        if ($this->xInclude && $document->xinclude() === -1) {
            $this->collectXmlErrors($errors, $document);
        }

        if ($this->dtdValidation && !$this->validateDTD($document)) {
            $this->collectXmlErrors($errors, $document);
        }

        $this->checkInternalSchemes($file, $document, $errors);

        $this->useInternalXmlLogging($useInternalErrors);

        return $errors;
    }

    #[Override]
    public function isInstalled(): bool
    {
        $extensions = get_loaded_extensions();

        return in_array('libxml', $extensions, true) && \in_array('dom', $extensions, true);
    }

    public function setLoadFromNet(bool $loadFromNet): void
    {
        $this->loadFromNet = $loadFromNet;
    }

    public function setXInclude(bool $xInclude): void
    {
        $this->xInclude = $xInclude;
    }

    public function setDtdValidation(bool $dtdValidation): void
    {
        $this->dtdValidation = $dtdValidation;
    }

    public function setSchemeValidation(bool $schemeValidation): void
    {
        $this->schemeValidation = $schemeValidation;
    }

    private function checkInternalSchemes(
        SplFileInfo $file,
        DOMDocument $document,
        LintErrorsCollection $errors,
    ): void {
        if (!$this->schemeValidation) {
            return;
        }
        $result = $this->validateInternalSchemes($file, $document, $errors);
        if ($result === true) {
            return;
        }

        $this->collectXmlErrors($errors, $document);
    }

    private function useInternalXmlLogging(bool $useInternalErrors): bool
    {
        return libxml_use_internal_errors($useInternalErrors);
    }

    private function loadDocument(SplFileInfo $file): ?DOMDocument
    {
        $this->registerXmlStreamContext();

        $document = new DOMDocument();
        $document->resolveExternals = $this->loadFromNet;
        $document->preserveWhiteSpace = false;
        $document->formatOutput = false;
        $loaded = $document->load($file->getPathname());

        return $loaded ? $document : null;
    }

    /**
     * This is added to fix a bug with remote DTDs that are blocking automated php request on some domains:.
     *
     * @see http://stackoverflow.com/questions/4062792/domdocumentvalidate-problem
     * @see https://bugs.php.net/bug.php?id=48080
     */
    private function registerXmlStreamContext(): void
    {
        libxml_set_streams_context(stream_context_create([
            'http' => [
                'user_agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.10; rv:43.0) Gecko/20100101 Firefox/43.0',
            ],
        ]));
    }

    private function collectXmlErrors(LintErrorsCollection $errors, ?DOMDocument $document): void
    {
        foreach (libxml_get_errors() as $error) {
            $this->addError(
                $errors,
                $this->getErrorType($error),
                $error->message,
                trim($error->file) !== '' ? $error->file : $document->documentURI ?? 'unknown',
                $error->code,
                $error->line,
                $error->column,
            );
        }
        $this->flushXmlErrors();
    }

    private function addError(
        LintErrorsCollection $errors,
        string $type,
        string $error,
        string $file,
        int $code = 0,
        int $line = 0,
        int $column = 0,
    ): void {
        $lintError = new XmlLintError(
            $type,
            $code,
            $error,
            $file,
            $line,
            $column,
        );
        $errors->add($lintError);
    }

    private function getErrorType(LibXMLError $error): string
    {
        return match ($error->level) {
            LIBXML_ERR_WARNING => LintError::TYPE_WARNING,
            LIBXML_ERR_FATAL => LintError::TYPE_FATAL,
            LIBXML_ERR_ERROR => LintError::TYPE_ERROR,
            default => LintError::TYPE_NONE,
        };
    }

    /**
     * Make sure the libxml errors are flushed and won't be occurring again.
     */
    private function flushXmlErrors(): void
    {
        libxml_clear_errors();
    }

    private function validateDTD(DOMDocument $document): bool
    {
        /** @psalm-suppress TypeDoesNotContainNull */
        if (null === $document->doctype) {
            return true;
        }

        // Do not validate external DTDs if the loadFromNet option is disabled:
        $systemId = $document->doctype->systemId;
        if (!$this->loadFromNet && filter_var($systemId, FILTER_VALIDATE_URL)) {
            return true;
        }

        return $document->validate();
    }

    private function validateInternalSchemes(
        SplFileInfo $file,
        DOMDocument $document,
        LintErrorsCollection $errors,
    ): bool {
        $schemas = $this->getSchemas($file, $document, $errors);
        if ($schemas === []) {
            return true;
        }

        $schemas = array_map(
            fn (string $scheme): ?string => $this->locateScheme($file, $scheme, $this->loadFromNet),
            $schemas,
        );
        $schemas = array_filter($schemas);
        if ($schemas === []) {
            $this->addError(
                $errors,
                LintError::TYPE_FATAL,
                'missing schemas to validate against',
                $file->getPathname(),
            );

            return false;
        }

        $isValid = true;
        foreach ($schemas as $scheme) {
            $isValid = $isValid && $document->schemaValidate($scheme);
        }

        return $isValid;
    }

    /**
     * @return array<string>
     */
    private function getSchemas(
        SplFileInfo $file,
        DOMDocument $document,
        LintErrorsCollection $errors,
    ): array {
        $schemas = [];
        $schemas = $this->addSchemasFromSchemaLocation(
            $file,
            $document,
            $errors,
            $schemas,
        );

        $schemaLocNoNamespace = $document->documentElement?->attributes->getNamedItemNS(
            self::XSI_NAMESPACE,
            'noNamespaceSchemaLocation',
        );
        if ($schemaLocNoNamespace !== null) {
            /**
             * @var array<string> $withoutNamespace
             * @phpstan-ignore varTag.nativeType
             */
            $withoutNamespace = preg_split('/\s+/', trim($schemaLocNoNamespace->textContent));
            $schemas = array_merge($schemas, $withoutNamespace);
        }

        return $schemas;
    }

    /**
     * @param array<string> $schemas
     * @return array<string>
     */
    private function addSchemasFromSchemaLocation(
        SplFileInfo $file,
        DOMDocument $document,
        LintErrorsCollection $errors,
        array $schemas,
    ): array {
        $schemaLocation = $document->documentElement?->attributes->getNamedItem('schemaLocation');
        if ($schemaLocation === null) {
            return $schemas;
        }

        if ($schemaLocation->namespaceURI !== self::XSI_NAMESPACE) {
            $this->addError(
                $errors,
                LintError::TYPE_FATAL,
                'schemaLocation attribute is not in the XML Schema Instance namespace',
                $file->getPathname(),
            );

            return $schemas;
        }

        /** @var array<int, string> $parts */
        $parts = preg_split('/\s+/', trim($schemaLocation->textContent)); // @phpstan-ignore varTag.nativeType
        if (count($parts) % 2 !== 0) {
            $this->addError(
                $errors,
                LintError::TYPE_FATAL,
                'schemaLocation must contain an even number of URI entries',
                $file->getPathname(),
            );

            return $schemas;
        }

        foreach ($parts as $key => $value) {
            $schemas = $this->addSchemasFromSchemaLocationPart(
                $file,
                $document,
                $errors,
                $schemas,
                $key,
                $value,
            );
        }

        return $schemas;
    }

    /**
     * @param array<string> $schemas
     * @return array<string>
     */
    private function addSchemasFromSchemaLocationPart(
        SplFileInfo $file,
        DOMDocument $document,
        LintErrorsCollection $errors,
        array $schemas,
        int $key,
        string $value,
    ): array {
        if ($key & 1) {
            $schemas[] = $value;

            return $schemas;
        }

        if ($value !== $document->documentElement?->namespaceURI) {
            $this->addError(
                $errors,
                LintError::TYPE_FATAL,
                sprintf(
                    'Namespace "%s" from schemaLocation is not declared in the document',
                    $value,
                ),
                $file->getPathname(),
            );
        }

        return $schemas;
    }

    private function locateScheme(SplFileInfo $xmlFile, string $scheme, bool $loadFromNet): ?string
    {
        if (filter_var($scheme, FILTER_VALIDATE_URL)) {
            return $loadFromNet ? $scheme : null;
        }

        $xmlFilePath = $xmlFile->getPath();
        $schemePath = $xmlFilePath === '' ? $scheme : rtrim($xmlFilePath, '/') . DIRECTORY_SEPARATOR . $scheme;
        $schemeFile = new SplFileInfo($schemePath);

        return $schemeFile->isReadable() ? $schemeFile->getPathname() : null;
    }
}
