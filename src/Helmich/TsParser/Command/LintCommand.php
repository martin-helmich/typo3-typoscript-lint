<?php
namespace Helmich\TsParser\Command;


use Helmich\TsParser\Linter\Configuration\ConfigurationLocator;
use Helmich\TsParser\Linter\LinterInterface;
use Helmich\TsParser\Linter\Report\Report;
use Helmich\TsParser\Linter\ReportPrinter\ConsoleReportPrinter;
use Helmich\TsParser\Linter\ReportPrinter\PrinterLocator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\StreamOutput;

class LintCommand extends Command
{



    /** @var \Helmich\TsParser\Linter\LinterInterface */
    private $linter;


    /** @var \Helmich\TsParser\Linter\Configuration\ConfigurationLocator */
    private $linterConfigurationLocator;


    /** @var \Helmich\TsParser\Linter\ReportPrinter\PrinterLocator */
    private $printerLocator;



    public function injectLinter(LinterInterface $linter)
    {
        $this->linter = $linter;
    }



    public function injectLinterConfigurationLocator(ConfigurationLocator $configurationLocator)
    {
        $this->linterConfigurationLocator = $configurationLocator;
    }



    public function injectReportPrinterLocator(PrinterLocator $printerLocator)
    {
        $this->printerLocator = $printerLocator;
    }



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



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $output->writeln("Linting input file <comment>{$filename}</comment>.");

        $reportOutput = $input->getOption('output') === '-'
            ? $output
            : new StreamOutput(fopen($input->getOption('output'), 'w'));

        $configuration = $this->linterConfigurationLocator->loadConfiguration($input->getOption('config'), $output);
        $printer       = $this->printerLocator->createPrinter($input->getOption('format'), $reportOutput);
        $report        = new Report();

        $this->linter->lintFile($filename, $report, $configuration, $output);

        $printer->writeReport($report);
    }


}