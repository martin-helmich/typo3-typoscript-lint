<?php
namespace Helmich\TsParser\Command;


use Helmich\TsParser\Linter\Configuration\ConfigurationLocator;
use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\LinterInterface;
use Helmich\TsParser\Linter\Report\Report;
use Helmich\TsParser\Linter\ReportPrinter\CheckstyleReportPrinter;
use Helmich\TsParser\Linter\ReportPrinter\ConsoleReportPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class LintCommand extends Command
{



    /**
     * @var \Helmich\TsParser\Linter\LinterInterface
     */
    private $linter;


    /**
     * @var \Helmich\TsParser\Linter\Configuration\ConfigurationLocator
     */
    private $linterConfigurationLocator;



    public function injectLinter(LinterInterface $linter)
    {
        $this->linter = $linter;
    }



    public function injectLinterConfigurationLocator(ConfigurationLocator $configurationLocator)
    {
        $this->linterConfigurationLocator = $configurationLocator;
    }



    protected function configure()
    {
        $this
            ->setName('lint')
            ->setDescription('Check coding style for TypoScript file.')
            ->addOption('config', 'c', InputOption::VALUE_REQUIRED, 'Configuration file to use.')
            ->addOption('output', 'o', InputOption::VALUE_REQUIRED, 'Output format.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $output->writeln("Linting input file <comment>{$filename}</comment>.");

        $configuration = $this->linterConfigurationLocator->loadConfiguration($input->getOption('config'), $output);
        $printer = new CheckstyleReportPrinter($output);
        $printer = new ConsoleReportPrinter($output);
        $report  = new Report();

        $this->linter->lintFile($filename, $report, $configuration, $output);

        $printer->writeReport($report);

        #$tokens = $this->tokenizer->tokenizeStream($filename);

        #$output->write($this->tokenPrinter->printTokenStream($tokens));
    }


}