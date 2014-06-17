<?php
namespace Helmich\TsParser\Command;


use Helmich\TsParser\Linter\Configuration\ConfigurationLocator;
use Helmich\TsParser\Linter\LinterConfiguration;
use Helmich\TsParser\Linter\LinterInterface;
use Helmich\TsParser\Linter\Report\Report;
use Helmich\TsParser\Linter\ReportPrinter\CheckstyleReportPrinter;
use Helmich\TsParser\Linter\ReportPrinter\ConsoleReportPrinter;
use Helmich\TsParser\Parser\Parser;
use Helmich\TsParser\Parser\Printer\PrettyPrinter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ParseCommand extends Command
{



    /**
     * @var \Helmich\TsParser\Parser\Parser
     */
    private $parser;



    public function injectParser(Parser $parser)
    {
        $this->parser = $parser;
    }



    protected function configure()
    {
        $this
            ->setName('parse')
            ->setDescription('Parse TypoScript file into syntax tree.')
            ->addArgument('filename', InputArgument::REQUIRED, 'Input filename');
    }



    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $filename = $input->getArgument('filename');

        $printer = new PrettyPrinter();
        $statements = $this->parser->parse(file_get_contents($filename));

        $printer->printStatements($statements, $output);
    }


}