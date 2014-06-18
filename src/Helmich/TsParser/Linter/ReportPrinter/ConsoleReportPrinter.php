<?php
namespace Helmich\TsParser\Linter\ReportPrinter;


use Helmich\TsParser\Linter\Report\Report;
use Helmich\TsParser\Linter\Report\Warning;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Report printer that prints a report in human-readable form.
 *
 * These reports are useful for manual application. For generating
 * machine-readable output, have a look at the CheckstyleReportPrinter
 * class.
 *
 * @package    Helmich\TsParser
 * @subpackage Linter\ReportPrinter
 */
class ConsoleReportPrinter implements Printer
{



    /** @var \Symfony\Component\Console\Output\OutputInterface */
    private $output;



    /**
     * Constructs a new console report printer.
     *
     * @param \Symfony\Component\Console\Output\OutputInterface $output The output stream to write on.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }



    /**
     * Writes a report in human-readable table form.
     *
     * @param \Helmich\TsParser\Linter\Report\Report $report The report to print.
     * @return void
     */
    public function writeReport(Report $report)
    {
        $count = 0;

        $this->output->writeln('');
        $this->output->writeln('<comment>CHECKSTYLE REPORT</comment>');

        $styleMap = [
            Warning::SEVERITY_ERROR => 'error',
            Warning::SEVERITY_WARNING => 'comment',
            Warning::SEVERITY_INFO => 'info',
        ];

        foreach ($report->getFiles() as $file)
        {
            $this->output->writeln("=> <comment>{$file->getFilename()}</comment>.");
            foreach ($file->getWarnings() as $warning)
            {
                $count++;

                $style = $styleMap[$warning->getSeverity()];

                $this->output->writeln(
                    sprintf('<comment>%4d <%s> %s </%s></comment>', $warning->getLine(), $style, $warning->getMessage(), $style)
                );
            }
        }

        $this->output->writeln("");
        $this->output->writeln('<comment>SUMMARY</comment>');
        $this->output->writeln("<info><comment>$count</comment> warnings in total.</info>");
    }

}