<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractLinterTask;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Override;
use PixelFederation\CodingStandards\GrumPHP\Linter\Xml\XmlLinter;

/**
 * @see \GrumPHP\Task\XmlLint
 * @extends AbstractLinterTask<XmlLinter>
 */
final class XmlLintExtendedTask extends AbstractLinterTask
{
    #[Override]
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = self::sharedOptionsResolver();
        $resolver->setDefaults([
            'dtd_validation' => false,
            'load_from_net' => false,
            'scheme_validation' => false,
            'triggered_by' => ['xml'],
            'x_include' => false,
        ]);

        $resolver->addAllowedTypes('load_from_net', ['bool']);
        $resolver->addAllowedTypes('x_include', ['bool']);
        $resolver->addAllowedTypes('dtd_validation', ['bool']);
        $resolver->addAllowedTypes('scheme_validation', ['bool']);
        $resolver->addAllowedTypes('triggered_by', ['array']);

        return ConfigOptionsResolver::fromClosure(
            static fn (array $options): array => $resolver->resolve($options),
        );
    }

    #[Override]
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    #[Override]
    public function run(ContextInterface $context): TaskResultInterface
    {
        /**
         * @var array{
         *     dtd_validation: bool,
         *     load_from_net: bool,
         *     scheme_validation: bool,
         *     triggered_by: array<string>,
         *     x_include: bool,
         * } $config
         */
        $config = $this->getConfig()->getOptions();
        $files = $context->getFiles()->extensions($config['triggered_by']);
        if (count($files) === 0) {
            return TaskResult::createSkipped($this, $context);
        }

        $this->linter->setLoadFromNet($config['load_from_net']);
        $this->linter->setXInclude($config['x_include']);
        $this->linter->setDtdValidation($config['dtd_validation']);
        $this->linter->setSchemeValidation($config['scheme_validation']);

        try {
            $lintErrors = $this->lint($files);
        } catch (RuntimeException $e) {
            return TaskResult::createFailed($this, $context, $e->getMessage());
        }

        if ($lintErrors->count()) {
            return TaskResult::createFailed(
                $this,
                $context,
                sprintf("%s\nErrors: %d", $lintErrors, $lintErrors->count()),
            );
        }

        return TaskResult::createPassed($this, $context);
    }
}
