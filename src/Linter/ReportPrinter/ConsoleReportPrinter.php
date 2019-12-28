<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
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
    public function writeReport(Report $report): void
    {
        $count = 0;
        $fixableCount = 0;

        $this->output->writeln('');
        $this->output->writeln('<comment>CHECKSTYLE REPORT</comment>');

        $styleMap = [
            Issue::SEVERITY_ERROR   => 'error',
            Issue::SEVERITY_WARNING => 'comment',
            Issue::SEVERITY_INFO    => 'info',
        ];

        $styleLabelMap = [
            Issue::SEVERITY_ERROR   => "ERROR",
            Issue::SEVERITY_WARNING => "WARN",
            Issue::SEVERITY_INFO    => "INFO",
        ];

        foreach ($report->getFiles() as $file) {
            $relativeFilename = PathUtils::getRelativePath($file->getFilename());

            $this->output->writeln("");
            $this->output->writeln("=> <comment>{$relativeFilename}</comment>");

            foreach ($file->getIssues() as $issue) {
                $count++;

                $style = $styleMap[$issue->getSeverity()];
                $label = $styleLabelMap[$issue->getSeverity()];

                $level = sprintf("<{$style}>%5s</{$style}>", $label);

                $this->output->writeln(
                    sprintf(
                        '    %s %4d %s%s',
                        $level,
                        $issue->getLine() ?? 0,
                        $issue->getMessage(),
                        $issue->isFixable() ? " <info>(FIXABLE)</info>" : ""
                    )
                );

                if ($issue->isFixable()) {
                    $fixableCount ++;
                }
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
        $this->output->write("  <info><comment>$count</comment> issues in total.</info>");

        if ($count > 0) {
            $this->output->writeln(" (" . implode(', ', $summary) . ")");
        } else {
            $this->output->writeln("");
        }

        if ($fixableCount > 0) {
            $this->output->writeln("");
            $this->output->writeln('<comment>AUTOMATIC FIXING</comment>');
            $this->output->writeln("  <comment>{$fixableCount}</comment> issues can be fixed automatically.");
            $this->output->writeln("  Run this tool with <comment>--fix</comment> to preview possible fixes.");
        }
    }
}
