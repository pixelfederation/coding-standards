<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Task;

use GrumPHP\Exception\RuntimeException;
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
final class ComposerInstallCheckTask extends AbstractExternalTask
{
    #[Override]
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'ignore_patterns' => [],
            'script' => './vendor/pixelfederation/coding-standards/bin/composer_install_check.sh',
            'triggered_by' => ['php', 'yml', 'yaml', 'xml'],
            'whitelist_patterns' => [],
        ]);

        $resolver->addAllowedTypes('script', ['string']);
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
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
         *     script: string,
         *     ignore_patterns: array<string>,
         *     whitelist_patterns: array<string>,
         *     triggered_by: array<string>,
         * } $config
         */
        $config = $this->getConfig()->getOptions();

        $files = $context->getFiles()
            ->extensions($config['triggered_by'])
            ->paths($config['whitelist_patterns'] ?? [])
            ->notPaths($config['ignore_patterns'] ?? []);
        if (count($files) === 0) {
            return TaskResult::createSkipped($this, $context);
        }

        $exceptions = [];
        try {
            $this->runShell($config['script']);
        } catch (RuntimeException $e) {
            $exceptions[] = $e->getMessage();
        }

        if (count($exceptions)) {
            return TaskResult::createFailed($this, $context, implode(PHP_EOL, $exceptions));
        }

        return TaskResult::createPassed($this, $context);
    }

    private function runShell(string $scriptPath): void
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('sh');
        $arguments->addRequiredArgument('%s', $scriptPath);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new RuntimeException($this->formatter->format($process));
        }
    }
}
