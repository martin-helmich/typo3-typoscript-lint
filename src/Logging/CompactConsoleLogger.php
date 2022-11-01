<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Logging;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\Printer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Compact console logger
 *
 * This logger prints a compact progress report to the console, similar to PHPUnit.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Logging
 */
class CompactConsoleLogger implements LinterLoggerInterface
{
    const OUTPUT_WIDTH = 50;

    /** @var int */
    private $fileCount = 0;

    /** @var int */
    private $issueCount = 0;

    /** @var int */
    private $fileCompletedCount = 0;

    /** @var OutputInterface */
    private $output;

    /** @var string */
    private $progressFormatString = "   [%3d / %-3d, %3d%%]";

    /** @var Printer */
    private $printer;

    public function __construct(Printer $printer, OutputInterface $output)
    {
        $this->output = $output;
        $this->printer = $printer;
    }

    public function notifyFileNotFound(string $file): void
    {
        $this->output->writeln("<error>WARNING: Input file ${file} does not seem to exist.</error>");
    }

    public function notifyFiles(array $files): void
    {
        $this->fileCount = count($files);

        $numCount = strlen("" . $this->fileCount);
        $this->progressFormatString = "   [%{$numCount}d / %-{$numCount}d, %3d%%]";
    }

    public function notifyFileStart(string $filename): void
    {
    }

    public function notifyFileSniffStart(string $filename, string $sniffClass): void
    {
    }

    public function nofifyFileSniffComplete(string $filename, string $sniffClass, File $report): void
    {
    }

    public function notifyFileComplete(string $filename, File $report): void
    {
        if (count($report->getIssuesBySeverity(Issue::SEVERITY_ERROR)) > 0) {
            $this->output->write("<error>E</error>");
        } elseif (count($report->getIssuesBySeverity(Issue::SEVERITY_WARNING)) > 0) {
            $this->output->write("<comment>W</comment>");
        } elseif (count($report->getIssuesBySeverity(Issue::SEVERITY_INFO)) > 0) {
            $this->output->write("<comment>I</comment>");
        } else {
            $this->output->write("<info>.</info>");
        }

        $this->fileCompletedCount += 1;
        $this->issueCount += count($report->getIssues());

        if ($this->fileCompletedCount % self::OUTPUT_WIDTH === 0) {
            $this->printProgress();
        }
    }

    private function printProgress(): void
    {
        $this->output->writeln(sprintf($this->progressFormatString, $this->fileCompletedCount, $this->fileCount,
            $this->fileCompletedCount / $this->fileCount * 100));
    }

    public function notifyRunComplete(Report $report): void
    {
        $remaining = $this->fileCompletedCount % self::OUTPUT_WIDTH;
        if ($remaining !== 0) {
            $this->output->write(str_repeat(' ', self::OUTPUT_WIDTH - $remaining));
            $this->printProgress();
        }

        $this->output->write("\n");

        if ($this->issueCount > 0) {
            $this->output->writeln("Completed with <comment>{$this->issueCount} issues</comment>");

            $this->printer->writeReport($report);
        } else {
            $this->output->writeln("Complete <info>without warnings</info>");
        }
    }
}
