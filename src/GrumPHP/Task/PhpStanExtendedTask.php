<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
use GrumPHP\Collection\ProcessArgumentsCollection;
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
 * @see \GrumPHP\Task\PhpStan
 * @psalm-type ConfigType = array{
 *     autoload_file: string|null,
 *     chunk_size: positive-int,
 *     configuration: string|null,
 *     level: string|int|null,
 *     ignore_patterns: array<array-key, string>,
 *     force_patterns: array<array-key, string>,
 *     triggered_by: array<array-key, string>,
 *     memory_limit: string|null,
 *     use_grumphp_paths: bool,
 * }
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
final class PhpStanExtendedTask extends AbstractExternalTask
{
    #[Override]
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'autoload_file' => null,
            'chunk_size' => 1000,
            'configuration' => null,
            'force_patterns' => [],
            'ignore_patterns' => [],
            'level' => null,
            'memory_limit' => null,
            'triggered_by' => ['php'],
            'use_grumphp_paths' => true,
        ]);

        $resolver->addAllowedTypes('autoload_file', ['null', 'string']);
        $resolver->setAllowedValues(
            'chunk_size',
            static function (mixed $value): bool {
                return false !== filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            },
        );
        $resolver->addAllowedTypes('configuration', ['null', 'string']);
        $resolver->addAllowedTypes('memory_limit', ['null', 'string']);
        $resolver->setAllowedValues(
            'level',
            static function (mixed $value): bool {
                if ($value === null || $value === 'max') {
                    return true;
                }

                return false !== filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 0]]);
            },
        );
        $resolver->addAllowedTypes('ignore_patterns', ['array']);
        $resolver->addAllowedTypes('force_patterns', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->addAllowedTypes('use_grumphp_paths', ['bool']);

        return ConfigOptionsResolver::fromOptionsResolver($resolver);
    }

    #[Override]
    public function canRunInContext(ContextInterface $context): bool
    {
        return $context instanceof GitPreCommitContext || $context instanceof RunContext;
    }

    #[Override]
    public function run(ContextInterface $context): TaskResultInterface
    {
        // phpcs:ignore SlevomatCodingStandard.PHP.RequireExplicitAssertion.RequiredExplicitAssertion
        /** @var ConfigType $config */
        $config = $this->getConfig()->getOptions();
        $files = $this->getFiles($context, $config);

        if (!$config['use_grumphp_paths']) {
            return $this->processWithoutGrumphpPaths($context, $config);
        }

        if (count($files) === 0) {
            return TaskResult::createSkipped($this, $context);
        }

        $chunkSize = $config['chunk_size'];
        $chunks = array_chunk($files->toArray(), $chunkSize);
        $totalChunks = count($chunks);
        foreach ($chunks as $index => $chunk) {
            $arguments = $this->createArguments(new FilesCollection($chunk), $config);
            $process = $this->processBuilder->buildProcess($arguments);
            $process->run();

            if (!$process->isSuccessful()) {
                $message = sprintf(
                    'Chunk %d/%d failed:%s%s',
                    $index + 1,
                    $totalChunks,
                    PHP_EOL,
                    $this->formatter->format($process),
                );

                return TaskResult::createFailed($this, $context, $message);
            }
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param ConfigType $config
     */
    private function processWithoutGrumphpPaths(ContextInterface $context, array $config): TaskResultInterface
    {
        $arguments = $this->createArguments(null, $config);
        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();
        if (!$process->isSuccessful()) {
            return TaskResult::createFailed($this, $context, $this->formatter->format($process));
        }

        return TaskResult::createPassed($this, $context);
    }

    /**
     * @param ConfigType $config
     */
    private function getFiles(ContextInterface $context, array $config): FilesCollection
    {
        $files = $context
            ->getFiles()
            ->notPaths($config['ignore_patterns'])
            ->extensions($config['triggered_by']);

        if ($config['force_patterns'] !== []) {
            $forcedFiles = $context->getFiles()->paths($config['force_patterns']);
            $files = $files->ensureFiles($forcedFiles);
        }

        return $files;
    }

    /**
     * @param ConfigType $config
     */
    private function createArguments(?FilesCollection $files, array $config): ProcessArgumentsCollection
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('phpstan');

        $arguments->add('analyse');
        $arguments->addOptionalArgument('--autoload-file=%s', $config['autoload_file']);
        $arguments->addOptionalArgument('--configuration=%s', $config['configuration']);
        $arguments->addOptionalArgument('--memory-limit=%s', $config['memory_limit']);
        $arguments->addOptionalMixedArgument('--level=%s', $config['level']);
        $arguments->add('--no-ansi');
        $arguments->add('--no-interaction');
        $arguments->add('--no-progress');

        if ($files) {
            $arguments->addFiles($files);
        }

        return $arguments;
    }
}
