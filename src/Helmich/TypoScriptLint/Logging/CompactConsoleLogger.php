<?php
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
use Symfony\Component\Console\Output\OutputInterface;

class CompactConsoleLogger implements LinterLoggerInterface
{
    const OUTPUT_WIDTH = 50;

    /** @var int */
    private $fileCount;

    private $warningCount = 0;

    private $fileCompletedCount = 0;

    /** @var OutputInterface */
    private $output;

    /** @var string */
    private $progressFormatString;

    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    public function notifyFiles(array $files)
    {
        $this->fileCount = count($files);

        $numCount = strlen("" . $this->fileCount);
        $this->progressFormatString = "   [%{$numCount}d / %-{$numCount}d, %3d%%]";
    }

    public function notifyFileStart($filename)
    {
        // TODO: Implement notifyFileStart() method.
    }

    public function notifyFileSniffStart($filename, $sniffClass)
    {
        // TODO: Implement notifyFileSniffStart() method.
    }

    public function nofifyFileSniffComplete($filename, $sniffClass, File $report)
    {
        // TODO: Implement nofifyFileSniffComplete() method.
    }

    public function notifyFileComplete($filename, File $report)
    {
        if (count($report->getWarnings()) > 0) {
            $this->output->write("<comment>W</comment>");
        } else {
            $this->output->write("<info>.</info>");
        }

        $this->fileCompletedCount += 1;
        $this->warningCount += count($report->getWarnings());

        if ($this->fileCompletedCount % self::OUTPUT_WIDTH === 0) {
            $this->printProgress();
        }
    }

    private function printProgress()
    {
        $this->output->writeln(sprintf($this->progressFormatString, $this->fileCompletedCount, $this->fileCount, $this->fileCompletedCount / $this->fileCount * 100));
    }

    public function notifyRunComplete(Report $report)
    {
        $remaining = $this->fileCompletedCount % self::OUTPUT_WIDTH;
        if ($remaining !== 0) {
            $this->output->write(str_repeat(' ', self::OUTPUT_WIDTH - $remaining));
            $this->printProgress();
        }

        $this->output->write("\n");

        if ($this->warningCount > 0) {
            $this->output->writeln("Completed with <comment>{$this->warningCount} warnings</comment>");

            (new ConsoleReportPrinter($this->output))->writeReport($report);
        } else {
            $this->output->writeln("Complete <info>without warnings</info>");
        }
    }
}