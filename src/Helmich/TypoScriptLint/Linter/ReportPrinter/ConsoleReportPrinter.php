<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Report printer that prints a report in human-readable form.
 *
 * These reports are useful for manual application. For generating
 * machine-readable output, have a look at the CheckstyleReportPrinter
 * class.
 *
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\ReportPrinter
 */
class ConsoleReportPrinter implements Printer
{

    /** @var OutputInterface */
    private $output;

    /**
     * Constructs a new console report printer.
     *
     * @param OutputInterface $output The output stream to write on.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a report in human-readable table form.
     *
     * @param Report $report The report to print.
     * @return void
     */
    public function writeReport(Report $report)
    {
        $count = 0;

        $this->output->writeln('');
        $this->output->writeln('<comment>CHECKSTYLE REPORT</comment>');

        $styleMap = [
            Issue::SEVERITY_ERROR   => 'error',
            Issue::SEVERITY_WARNING => 'comment',
            Issue::SEVERITY_INFO    => 'info',
        ];

        foreach ($report->getFiles() as $file) {
            $this->output->writeln("=> <comment>{$file->getFilename()}</comment>.");
            foreach ($file->getIssues() as $issue) {
                $count++;

                $style = $styleMap[$issue->getSeverity()];

                $this->output->writeln(
                    sprintf(
                        '<comment>%4d <%s>%s</%s></comment>',
                        $issue->getLine(),
                        $style,
                        $issue->getMessage(),
                        $style
                    )
                );
            }
        }

        $summary = [];

        foreach ($styleMap as $severity => $style) {
            $severityCount = $report->countIssuesBySeverity($severity);
            if ($severityCount > 0) {
                $summary[] = "<comment>$severityCount</comment> {$severity}s";
            }
        }

        $this->output->writeln("");
        $this->output->writeln('<comment>SUMMARY</comment>');
        $this->output->write("<info><comment>$count</comment> issues in total.</info>");

        if ($count > 0) {
            $this->output->writeln(" (" . implode(', ', $summary) . ")");
        }
    }
}
