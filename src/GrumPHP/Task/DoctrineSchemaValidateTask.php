<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Task;

use GrumPHP\Formatter\ProcessFormatterInterface;
use GrumPHP\Runner\TaskResult;
use GrumPHP\Runner\TaskResultInterface;
use GrumPHP\Task\AbstractExternalTask;
use GrumPHP\Task\Config\ConfigOptionsResolver;
use GrumPHP\Task\Context\ContextInterface;
use GrumPHP\Task\Context\GitPreCommitContext;
use GrumPHP\Task\Context\RunContext;
use Override;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
final class DoctrineSchemaValidateTask extends AbstractExternalTask
{
    #[Override]
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'console_path' => 'bin/console',
            'em' => null,
            'skip_mapping' => false,
            'skip_property_types' => null,
            'skip_sync' => false,
            'triggered_by' => ['php', 'xml', 'yml'],
        ]);

        $resolver->addAllowedTypes('skip_mapping', ['bool']);
        $resolver->addAllowedTypes('skip_sync', ['bool']);
        $resolver->addAllowedTypes('skip_property_types', ['null', 'bool']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('em', ['null', 'string']);

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
         *     console_path: string,
         *     skip_mapping: bool,
         *     skip_sync: bool,
         *     skip_property_types: bool|null,
         *     em: string|null,
         *     triggered_by: array<string>,
         * } $config
         */
        $config = $this->getConfig()->getOptions();
        $files = $context->getFiles()->extensions($config['triggered_by']);

        if (0 === \count($files)) {
            return TaskResult::createSkipped($this, $context);
        }

        $arguments = $this->processBuilder->createArgumentsForCommand('php');
        $arguments->add($config['console_path']);
        $arguments->add('doctrine:schema:validate');
        $arguments->addOptionalArgument('--skip-mapping', $config['skip_mapping']);
        $arguments->addOptionalArgument('--skip-sync', $config['skip_sync']);
        $skipPropertyTypes = $config['skip_property_types'] ?? null;
        if (is_bool($skipPropertyTypes)) {
            $arguments->addOptionalArgument('--skip-property-types', $config['skip_property_types']);
        }
        $em = $config['em'] ?? null;
        if (is_string($em)) {
            $arguments->addOptionalArgument('--em=%s', $em);
        }

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            $output = $this->formatter->format($process);

            return TaskResult::createFailed($this, $context, $output);
        }

        return TaskResult::createPassed($this, $context);
    }
}
