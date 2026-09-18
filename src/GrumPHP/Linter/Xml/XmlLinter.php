<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Linter\Xml;

use DOMDocument;
use DOMNode;
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

        try {
            $document = $this->loadDocument($file);
            if (!$document) {
                $this->collectXmlErrors($errors, null);

                return $errors;
            }

            if ($this->xInclude && $document->xinclude() === -1) {
                $this->collectXmlErrors($errors, $document);
            }

            if ($this->dtdValidation && !$this->validateDTD($document)) {
                $this->collectXmlErrors($errors, $document);
            }

            $this->checkInternalSchemes($file, $document, $errors);

            return $errors;
        } finally {
            $this->flushXmlErrors();
            $this->useInternalXmlLogging($useInternalErrors);
        }
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
                'header' => ['User-Agent: GrumPHP XML Schema Validator'],
            ],
            'https' => [
                'header' => ['User-Agent: GrumPHP XML Schema Validator'],
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
        $schemaLocation = $this->getSchemaLocation($file, $document, $errors);
        if ($schemaLocation === null) {
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

        $documentNamespace = $document->documentElement->namespaceURI ?? '';
        if ($documentNamespace === '') {
            return $schemas;
        }

        $schema = $this->findSchemaForNamespace($parts, $documentNamespace);
        if ($schema === null) {
            $this->addMissingSchemaError($file, $errors, $documentNamespace);

            return $schemas;
        }

        $schemas[] = $schema;

        return $schemas;
    }

    private function getSchemaLocation(
        SplFileInfo $file,
        DOMDocument $document,
        LintErrorsCollection $errors,
    ): ?DOMNode {
        $schemaLocation = $document->documentElement?->attributes->getNamedItemNS(
            self::XSI_NAMESPACE,
            'schemaLocation',
        );
        if ($schemaLocation !== null) {
            return $schemaLocation;
        }

        if ($document->documentElement?->attributes->getNamedItem('schemaLocation') !== null) {
            $this->addError(
                $errors,
                LintError::TYPE_FATAL,
                'schemaLocation attribute is not in the XML Schema Instance namespace',
                $file->getPathname(),
            );
        }

        return null;
    }

    /**
     * @param array<int, string> $parts
     */
    private function findSchemaForNamespace(array $parts, string $documentNamespace): ?string
    {
        for ($key = 0; $key < count($parts); $key += 2) {
            if ($parts[$key] === $documentNamespace) {
                return $parts[$key + 1];
            }
        }

        return null;
    }

    private function addMissingSchemaError(
        SplFileInfo $file,
        LintErrorsCollection $errors,
        string $documentNamespace,
    ): void {
        $this->addError(
            $errors,
            LintError::TYPE_FATAL,
            sprintf('Missing schema for document namespace "%s"', $documentNamespace),
            $file->getPathname(),
        );
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
