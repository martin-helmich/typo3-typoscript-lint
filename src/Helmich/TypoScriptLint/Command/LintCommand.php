<?php
namespace Helmich\TypoScriptLint\Command;

use Helmich\TypoScriptLint\Exception\BadOutputFileException;
use Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator;
use Helmich\TypoScriptLint\Linter\LinterInterface;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator;
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

    /** @var \Helmich\TypoScriptLint\Linter\LinterInterface */
    private $linter;

    /** @var \Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator */
    private $linterConfigurationLocator;

    /** @var \Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator */
    private $printerLocator;

    /** @var \Helmich\TypoScriptLint\Util\Finder */
    private $finder;

    /** @var \Symfony\Component\EventDispatcher\EventDispatcherInterface */
    private $eventDispatcher;

    /**
     * Injects a linter.
     *
     * @param \Helmich\TypoScriptLint\Linter\LinterInterface $linter The linter to use.
     * @return void
     */
    public function injectLinter(LinterInterface $linter)
    {
        $this->linter = $linter;
    }

    /**
     * Injects a locator for the linter configuration.
     *
     * @param \Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator $configurationLocator The configuration locator.
     * @return void
     */
    public function injectLinterConfigurationLocator(ConfigurationLocator $configurationLocator)
    {
        $this->linterConfigurationLocator = $configurationLocator;
    }

    /**
     * Injects a locator for report printers.
     *
     * @param \Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator $printerLocator A report printer locator.
     * @return void
     */
    public function injectReportPrinterLocator(PrinterLocator $printerLocator)
    {
        $this->printerLocator = $printerLocator;
    }

    /**
     * Injects a finder for finding files.
     *
     * @param \Helmich\TypoScriptLint\Util\Finder $finder The finder.
     * @return void
     */
    public function injectFinder(Finder $finder)
    {
        $this->finder = $finder;
    }

    public function injectEventDispatcher(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * Configures this command.
     *
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Check coding style for TypoScript file.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to use', 'tslint.yml')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format', 'text')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file ("-" for stdout)', '-')
            ->addOption(
                'exit-code',
                'e',
                InputOption::VALUE_NONE,
                'Set this flag to exit with a non-zero exit code when there are warnings'
            )
            ->addArgument('paths', InputArgument::OPTIONAL | InputArgument::IS_ARRAY, 'File or directory names. If omitted, the "paths" option from the configuration file will be used, if present');
    }

    /**
     * Executes this command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Input options.
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output stream.
     * @return void
     *
     * @throws \Helmich\TypoScriptLint\Exception\BadOutputFileException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $configuration    = $this->linterConfigurationLocator->loadConfiguration($input->getOption('config'));
        $paths            = $input->getArgument('paths') ?: $configuration->getPaths();
        $outputTarget     = $input->getOption('output');
        $exitWithExitCode = $input->getOption('exit-code');

        if (false == $outputTarget) {
            throw new BadOutputFileException('Bad output file.');
        }

        $reportOutput = $input->getOption('output') === '-'
            ? $output
            : new StreamOutput(fopen($input->getOption('output'), 'w'));

        $printer       = $this->printerLocator->createPrinter($input->getOption('format'), $reportOutput);
        $report        = new Report();

        $patterns = $configuration->getFilePatterns();

        foreach ($this->finder->getFilenames($paths, $patterns) as $filename) {
            $output->writeln("Linting input file <comment>{$filename}</comment>.");
            $this->linter->lintFile($filename, $report, $configuration, $output);
        }

        $printer->writeReport($report);

        if ($exitWithExitCode) {
            $exitCode = ($report->countWarnings() > 0) ? 2 : 0;
            $this->eventDispatcher->addListener(
                ConsoleEvents::TERMINATE,
                function (ConsoleTerminateEvent $event) use ($exitCode) {
                    $event->setExitCode($exitCode);
                }
            );
        }
    }
}
