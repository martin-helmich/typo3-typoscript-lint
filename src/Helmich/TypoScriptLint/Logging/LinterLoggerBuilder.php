<?php
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LinterLoggerBuilder
{

    /**
     * Finds a suitable printer for printing lint results.
     *
     * @param string          $outputFormat  The desired output format, as specified by the user, e.g. via command-line parameter
     * @param OutputInterface $reportOutput  Output stream for the result report (usually STDOUT or a file)
     * @param OutputInterface $consoleOutput Output stream for console data (usually STDOUT)
     * @return LinterLoggerInterface The printer matching the user's specifications.
     */
    public function createLogger($outputFormat, OutputInterface $reportOutput, OutputInterface $consoleOutput)
    {
        $errorOutput = ($consoleOutput instanceof ConsoleOutputInterface)
            ? $consoleOutput->getErrorOutput()
            : $consoleOutput;

        switch ($outputFormat) {
            case 'checkstyle':
            case 'xml':
                return new CompactConsoleLogger(new CheckstyleReportPrinter($reportOutput), $errorOutput);
            case 'txt':
            case 'text':
                return new VerboseConsoleLogger(new ConsoleReportPrinter($reportOutput), $consoleOutput);
            case 'compact':
                return new CompactConsoleLogger(new ConsoleReportPrinter($reportOutput), $consoleOutput);
            default:
                throw new \InvalidArgumentException('Invalid report printer "' . $outputFormat . '"!');
        }
    }
}