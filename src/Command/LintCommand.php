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

    /** @var LinterInterface */
    private $linter;

    /** @var ConfigurationLocator */
    private $linterConfigurationLocator;

    /** @var LinterLoggerBuilder */
    private $loggerBuilder;

    /** @var Finder */
    private $finder;

    /** @var EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * Injects a linter.
     *
     * @param LinterInterface $linter The linter to use.
     * @return void
     */
    public function injectLinter(LinterInterface $linter): void
    {
        $this->linter = $linter;
    }

    /**
     * Injects a locator for the linter configuration.
     *
     * @param ConfigurationLocator $configurationLocator The configuration locator.
     * @return void
     */
    public function injectLinterConfigurationLocator(ConfigurationLocator $configurationLocator): void
    {
        $this->linterConfigurationLocator = $configurationLocator;
    }

    /**
     * Injects a logger builder
     *
     * @param LinterLoggerBuilder $loggerBuilder A logger builder
     * @return void
     */
    public function injectLoggerBuilder(LinterLoggerBuilder $loggerBuilder): void
    {
        $this->loggerBuilder = $loggerBuilder;
    }

    /**
     * Injects a finder for finding files.
     *
     * @param Finder $finder The finder.
     * @return void
     */
    public function injectFinder(Finder $finder): void
    {
        $this->finder = $finder;
    }

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher): void
    {
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
            ->addOption('exit-code', 'e', InputOption::VALUE_NONE, '(DEPRECATED) Set this flag to exit with a non-zero exit code when there are warnings')
            ->addOption('fail-on-warnings', null, InputOption::VALUE_NONE, 'Set this flag to exit with a non-zero exit code when there are warnings')
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'File or directory names. If omitted, the "paths" option from the configuration file will be used, if present');
    }

    private function getPossibleConfigFiles(string $param): array
    {
        if ($param === 'typoscript-lint.yml') {
            return ["tslint.yml", "typoscript-lint.yml"];
        }

        return [$param];
    }

    /**
     * Executes this command.
     *
     * @param InputInterface  $input  Input options.
     * @param OutputInterface $output Output stream.
     * @return void
     *
     * @throws BadOutputFileException
     */
    protected function execute(InputInterface $input, OutputInterface $output): void
    {
        $configFilesOption = $input->getOption('config');
        $outputOption = $input->getOption('output');
        $formatOption = $input->getOption('format');

        '@phan-var string $configFilesOption';
        '@phan-var string $outputOption';
        '@phan-var string $formatOption';

        $configFiles       = $this->getPossibleConfigFiles($configFilesOption);
        $configuration     = $this->linterConfigurationLocator->loadConfiguration($configFiles);
        $paths             = $input->getArgument('paths') ?: $configuration->getPaths();
        $outputTarget      = $input->getOption('output');
        $exitWithExitCode  = $input->getOption('exit-code') || $input->getOption('fail-on-warnings');

        '@phan-var string[] $paths';

        if (false == $outputTarget) {
            throw new BadOutputFileException('Bad output file.');
        }

        if ($outputOption === '-') {
            $reportOutput = $output;
        } else {
            $fileHandle = fopen($outputOption, 'w');
            if ($fileHandle === false) {
                throw new \Exception("could not open file '${outputOption}' for writing");
            }

            $reportOutput = new StreamOutput($fileHandle);
        }

        $logger = $this->loggerBuilder->createLogger($formatOption, $reportOutput, $output);

        $report   = new Report();
        $patterns = $configuration->getFilePatterns();

        $files = $this->finder->getFilenames($paths, $patterns, new CallbackFinderObserver(function ($name) use ($logger) {
            $logger->notifyFileNotFound($name);
        }));
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
        } else if ($exitWithExitCode && $report->countIssues() > 0) {
            $exitCode = 2;
        }

        $this->eventDispatcher->addListener(
            ConsoleEvents::TERMINATE,
            function (ConsoleTerminateEvent $event) use ($exitCode) {
                $event->setExitCode($exitCode);
            }
        );
    }
}
