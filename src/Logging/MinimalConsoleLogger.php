<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Logging;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\Printer;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Minimal console logger
 *
 * This logger prints only the issues to the console.
 *
 * @author     Stefan Szymanski <stefan.szymanski@posteo.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Logging
 */
class MinimalConsoleLogger implements LinterLoggerInterface
{
    /** @var Printer */
    private $printer;

    /** @var OutputInterface */
    private $output;

    public function __construct(Printer $printer, OutputInterface $output)
    {
        $this->printer = $printer;
        $this->output = $output;
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
    }

    public function notifyFileSniffStart(string $filename, string $sniffClass): void
    {
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
