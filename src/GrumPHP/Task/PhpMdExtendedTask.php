<?php

declare(strict_types=1);

namespace PixelFederation\CodingStandards\GrumPHP\Task;

use GrumPHP\Collection\FilesCollection;
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
use Symfony\Component\Process\Process;

/**
 * @see \GrumPHP\Task\PhpMd
 * @psalm-type ConfigType = array{
 *     chunk_size: positive-int,
 *     exclude: array<array-key, string>,
 *     report_format: string,
 *     ruleset: array<array-key, string>,
 *     triggered_by: array<array-key, string>,
 *     whitelist_patterns: array<array-key, string>,
 * }
 * @extends AbstractExternalTask<ProcessFormatterInterface>
 */
final class PhpMdExtendedTask extends AbstractExternalTask
{
    #[Override]
    public static function getConfigurableOptions(): ConfigOptionsResolver
    {
        $resolver = new OptionsResolver();
        $resolver->setDefaults([
            'chunk_size' => 1000,
            'exclude' => [],
            'report_format' => 'text',
            'ruleset' => ['cleancode', 'codesize', 'naming'],
            'triggered_by' => ['php'],
            'whitelist_patterns' => [],
        ]);

        $resolver->addAllowedTypes('whitelist_patterns', ['array']);
        $resolver->addAllowedTypes('exclude', ['array']);
        $resolver->addAllowedTypes('report_format', ['string']);
        $resolver->addAllowedValues('report_format', ['text', 'ansi']);
        $resolver->addAllowedTypes('ruleset', ['array']);
        $resolver->addAllowedTypes('triggered_by', ['array']);
        $resolver->setAllowedValues(
            'chunk_size',
            static function (mixed $value): bool {
                return false !== filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
            },
        );

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
        if (count($files) === 0) {
            return TaskResult::createSkipped($this, $context);
        }

        $chunkSize = $config['chunk_size'];
        $chunks = array_chunk($files->toArray(), $chunkSize);
        $totalChunks = count($chunks);
        foreach ($chunks as $index => $chunk) {
            $process = $this->processChunk(new FilesCollection($chunk), $config);
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
    private function getFiles(ContextInterface $context, array $config): FilesCollection
    {
        $files = $context->getFiles();
        if (count($config['whitelist_patterns']) > 0) {
            $files = $files->paths($config['whitelist_patterns']);
        }

        return $files->extensions($config['triggered_by']);
    }

    /**
     * @param ConfigType $config
     */
    private function processChunk(FilesCollection $files, array $config): Process
    {
        $arguments = $this->processBuilder->createArgumentsForCommand('phpmd');
        $arguments->addCommaSeparatedFiles($files);
        $arguments->add($config['report_format']);
        $arguments->addOptionalCommaSeparatedArgument('%s', $config['ruleset']);
        $arguments->addOptionalArgument('--exclude', $config['exclude'] !== []);
        $arguments->addOptionalCommaSeparatedArgument('%s', $config['exclude']);

        $arguments->addOptionalArgument('--suffixes', $config['triggered_by'] !== []);
        $arguments->addOptionalCommaSeparatedArgument('%s', $config['triggered_by']);

        $process = $this->processBuilder->buildProcess($arguments);
        $process->run();

        return $process;
    }
}
