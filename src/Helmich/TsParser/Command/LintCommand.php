<?php
namespace Helmich\TsParser\Command;


use Helmich\TsParser\Linter\Configuration\ConfigurationLocator,
    Helmich\TsParser\Linter\LinterInterface,
    Helmich\TsParser\Linter\Report\Report,
    Helmich\TsParser\Linter\ReportPrinter\PrinterLocator;
use Symfony\Component\Console\Command\Command,
    Symfony\Component\Console\Input\InputArgument,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputOption,
    Symfony\Component\Console\Output\OutputInterface,
    Symfony\Component\Console\Output\StreamOutput;


/**
 * Command class that performs linting on a set of TypoScript files.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TsParser
 * @subpackage Command
 */
class LintCommand extends Command
{



    /** @var \Helmich\TsParser\Linter\LinterInterface */
    private $linter;


    /** @var \Helmich\TsParser\Linter\Configuration\ConfigurationLocator */
    private $linterConfigurationLocator;


    /** @var \Helmich\TsParser\Linter\ReportPrinter\PrinterLocator */
    private $printerLocator;



    /**
     * Injects a linter.
     *
     * @param \Helmich\TsParser\Linter\LinterInterface $linter The linter to use.
     * @return void
     */
    public function injectLinter(LinterInterface $linter)
    {
        $this->linter = $linter;
    }



    /**
     * Injects a locator for the linter configuration.
     *
     * @param \Helmich\TsParser\Linter\Configuration\ConfigurationLocator $configurationLocator The configuration locator.
     * @return void
     */
    public function injectLinterConfigurationLocator(ConfigurationLocator $configurationLocator)
    {
        $this->linterConfigurationLocator = $configurationLocator;
    }



    /**
     * Injects a locator for report printers.
     *
     * @param \Helmich\TsParser\Linter\ReportPrinter\PrinterLocator $printerLocator A report printer locator.
     * @return void
     */
    public function injectReportPrinterLocator(PrinterLocator $printerLocator)
    {
        $this->printerLocator = $printerLocator;
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
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to use.')
            ->addOption('format', 'f', InputOption::VALUE_REQUIRED, 'Output format.', 'text')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output file ("-" for stdout).', '-')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }



    /**
     * Executes this command.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input  Input options.
     * @param \Symfony\Component\Console\Output\OutputInterface $output Output stream.
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $output->writeln("Linting input file <comment>{$filename}</comment>.");

        $reportOutput = $input->getOption('output') === '-'
            ? $output
            : new StreamOutput(fopen($input->getOption('output'), 'w'));

        $configuration = $this->linterConfigurationLocator->loadConfiguration($input->getOption('config'));
        $printer       = $this->printerLocator->createPrinter($input->getOption('format'), $reportOutput);
        $report        = new Report();

        $this->linter->lintFile($filename, $report, $configuration, $output);

        $printer->writeReport($report);
    }


}