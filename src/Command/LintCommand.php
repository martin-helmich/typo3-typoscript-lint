<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Command;

use Helmich\TypoScriptLint\Exception\BadOutputFileException;
use Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator;
use Helmich\TypoScriptLint\Linter\LinterInterface;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Logging\LinterLoggerBuilder;
use Helmich\TypoScriptLint\Util\CallbackFinderObserver;
use Helmich\TypoScriptLint\Util\Finder;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\Console\Event\ConsoleTerminateEvent;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * Command class that performs linting on a set of TypoScript files.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Command
 */
class LintCommand extends Command
{

    private LinterInterface $linter;

    private ConfigurationLocator $linterConfigurationLocator;

    private LinterLoggerBuilder $loggerBuilder;

    private Finder $finder;

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(
        LinterInterface $linter,
        ConfigurationLocator $configurationLocator,
        LinterLoggerBuilder $loggerBuilder,
        Finder $finder,
        EventDispatcherInterface $eventDispatcher
    ) {
        parent::__construct();

        $this->linter = $linter;
        $this->linterConfigurationLocator = $configurationLocator;
        $this->loggerBuilder = $loggerBuilder;
        $this->finder = $finder;
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Configures this command.
     *
     * @return void
     */
    protected function configure(): void
    {
        $this
            ->setName('lint')
            ->setDescription('Check coding style for TypoScript file.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to use', 'typoscript-lint.yml')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format', 'compact')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file ("-" for stdout)', '-')
            ->addOption(
                'exit-code',
                'e',
                InputOption::VALUE_NONE,
                '(DEPRECATED) Set this flag to exit with a non-zero exit code when there are warnings')
            ->addOption(
                'fail-on-warnings',
                null,
                InputOption::VALUE_NONE,
                'Set this flag to exit with a non-zero exit code when there are warnings')
            ->addArgument(
                'paths',
                InputArgument::OPTIONAL | InputArgument::IS_ARRAY,
                'File or directory names. If omitted, the "paths" option from the configuration file will be used, if present'
            );
    }

    /**
     * @param string $fileName
     *
     * @return string[]
     */
    private function getPossibleConfigFiles(string $fileName): array
    {
        if ($fileName === 'typoscript-lint.yml') {
            return ["tslint.yml", "typoscript-lint.yml"];
        }

        return [$fileName];
    }

    /**
     * Executes this command.
     *
     * @param InputInterface $input Input options.
     * @param OutputInterface $output Output stream.
     *
     * @return int
     *
     * @throws BadOutputFileException
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** @var string $configFilesOption */
        $configFilesOption = $input->getOption('config');
        /** @var string $outputOption */
        $outputOption = $input->getOption('output');
        /** @var string $formatOption */
        $formatOption = $input->getOption('format');

        $configFiles = $this->getPossibleConfigFiles($configFilesOption);
        $configuration = $this->linterConfigurationLocator->loadConfiguration($configFiles);
        /** @var string[] $paths */
        $paths = $input->getArgument('paths') ?: $configuration->getPaths();
        $outputTarget = $input->getOption('output');
        $exitWithExitCode = $input->getOption('exit-code') || $input->getOption('fail-on-warnings');

        if (false == $outputTarget) {
            throw new BadOutputFileException('Bad output file.');
        }

        if ($outputOption === '-') {
            $reportOutput = $output;
        } else {
            $fileHandle = fopen($outputOption, 'w');
            if ($fileHandle === false) {
                throw new \Exception("could not open file '{$outputOption}' for writing");
            }

            $reportOutput = new StreamOutput($fileHandle);
        }

        $logger = $this->loggerBuilder->createLogger($formatOption, $reportOutput, $output);

        $report = new Report();
        $filePatterns = $configuration->getFilePatterns();
        $excludePatterns = $configuration->getExcludePatterns();
        $observer = new CallbackFinderObserver(function (string $name) use ($logger): void {
            $logger->notifyFileNotFound($name);
        });

        $files = $this->finder->getFilenames($paths, $filePatterns, $excludePatterns, $observer);
        $logger->notifyFiles($files);

        foreach ($files as $filename) {
            $logger->notifyFileStart($filename);
            $fileReport = $this->linter->lintFile($filename, $report, $configuration, $logger);
            $logger->notifyFileComplete($filename, $fileReport);
        }

        $logger->notifyRunComplete($report);

        $exitCode = 0;
        if ($report->countIssuesBySeverity(Issue::SEVERITY_ERROR) > 0) {
            $exitCode = 2;
        } elseif ($exitWithExitCode && $report->countIssues() > 0) {
            $exitCode = 2;
        }

        $this->eventDispatcher->addListener(
            ConsoleEvents::TERMINATE,
            function (ConsoleTerminateEvent $event) use ($exitCode): void {
                $event->setExitCode($exitCode);
            }
        );

        return $exitCode;
    }
}
