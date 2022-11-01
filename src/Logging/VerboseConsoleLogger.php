<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Logging;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\Printer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Verbose console logger
 *
 * This logger replaces the original, hard-coded default behaviour.
 *
 * Each and every one sniff is printed for each file. This behaviour was
 * mothballed since it was WAY to verbose for large projects and will likely
 * be entirely removed in later releases.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Logging
 */
class VerboseConsoleLogger implements LinterLoggerInterface
{
    /** @var OutputInterface */
    private $output;

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
    }

    public function notifyFileStart(string $filename): void
    {
        $this->output->writeln("Linting input file <comment>{$filename}</comment>.");
    }

    public function notifyFileSniffStart(string $filename, string $sniffClass): void
    {
        $this->output->writeln('=> <info>Executing sniff <comment>' . $sniffClass . '</comment>.</info>');
    }

    public function nofifyFileSniffComplete(string $filename, string $sniffClass, File $report): void
    {
    }

    public function notifyFileComplete(string $filename, File $report): void
    {
    }

    public function notifyRunComplete(Report $report): void
    {
        $this->printer->writeReport($report);
    }
}
