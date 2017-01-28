<?php
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
use Symfony\Component\Console\Output\OutputInterface;

class LinterLoggerBuilder
{

    /**
     * Finds a suitable printer for printing lint results.
     *
     * @param string          $outputFormat The desired output format, as specified by the user, e.g. via
     *                                                                        command-line parameter.
     * @param OutputInterface $output       The output stream (usually STDOUT or a file).
     * @return LinterLoggerInterface The printer matching the user's specifications.
     */
    public function createLogger($outputFormat, OutputInterface $reportOutput, OutputInterface $consoleOutput)
    {
        switch ($outputFormat) {
            //case 'checkstyle':
            //case 'xml':
            //    return new CheckstyleReportPrinter($output);
            case 'txt':
            case 'text':
                return new VerboseConsoleLogger(new ConsoleReportPrinter($reportOutput), $consoleOutput);
            case 'compact':
                return new CompactConsoleLogger($consoleOutput);
            default:
                throw new \InvalidArgumentException('Invalid report printer "' . $outputFormat . '"!');
        }
    }
}