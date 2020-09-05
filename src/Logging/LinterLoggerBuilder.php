<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\GccReportPrinter;
use Symfony\Component\Console\Output\ConsoleOutputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Helper class responsible for building a logger based on given input parameters
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Logging
 */
class LinterLoggerBuilder
{

    /**
     * Builds a suitable logger for logging lint progress and results.
     *
     * @param string          $outputFormat  The desired output format, as specified by the user, e.g. via command-line parameter
     * @param OutputInterface $reportOutput  Output stream for the result report (usually STDOUT or a file)
     * @param OutputInterface $consoleOutput Output stream for console data (usually STDOUT)
     * @return LinterLoggerInterface The printer matching the user's specifications.
     */
    public function createLogger(string $outputFormat, OutputInterface $reportOutput, OutputInterface $consoleOutput): LinterLoggerInterface
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
            case 'gcc':
                return new MinimalConsoleLogger(new GccReportPrinter($reportOutput), $errorOutput);
            default:
                throw new \InvalidArgumentException('Invalid report printer "' . $outputFormat . '"!');
        }
    }
}
