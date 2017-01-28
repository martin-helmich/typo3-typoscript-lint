<?php
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\Printer;
use Symfony\Component\Console\Output\OutputInterface;

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

    public function notifyFiles(array $files)
    {
        // TODO: Implement notifyFileCount() method.
    }

    public function notifyFileStart($filename)
    {
        $this->output->writeln("Linting input file <comment>{$filename}</comment>.");
    }

    public function notifyFileSniffStart($filename, $sniffClass)
    {
        $this->output->writeln('=> <info>Executing sniff <comment>' . $sniffClass . '</comment>.</info>');
    }

    public function nofifyFileSniffComplete($filename, $sniffClass, File $report)
    {
        // TODO: Implement nofifyFileSniffComplete() method.
    }

    public function notifyFileComplete($filename, File $report)
    {
        // TODO: Implement notifyFileComplete() method.
    }

    public function notifyRunComplete(Report $report)
    {
        $this->printer->writeReport($report);
    }
}