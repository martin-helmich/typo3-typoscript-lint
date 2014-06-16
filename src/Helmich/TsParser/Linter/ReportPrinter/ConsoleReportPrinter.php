<?php
namespace Helmich\TsParser\Linter\ReportPrinter;


use Helmich\TsParser\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleReportPrinter implements Printer
{



    /**
     * @var \Symfony\Component\Console\Output\OutputInterface
     */
    private $output;



    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }



    public function writeReport(Report $report)
    {
        $count = 0;

        $this->output->writeln('');
        $this->output->writeln('<comment>CHECKSTYLE REPORT</comment>');

        foreach ($report->getFiles() as $file)
        {
            $this->output->writeln("=> <comment>{$file->getFilename()}</comment>.");
            foreach ($file->getWarnings() as $warning)
            {
                $count ++;
                $this->output->writeln(sprintf('<comment>%4d <info>' . $warning->getMessage() . '</info>', $warning->getLine()));
            }
        }

        $this->output->writeln("");
        $this->output->writeln('<comment>SUMMARY</comment>');
        $this->output->writeln("<info><comment>$count</comment> warnings in total.</info>");
    }

}