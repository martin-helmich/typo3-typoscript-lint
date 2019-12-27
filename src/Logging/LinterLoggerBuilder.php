<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Logging;


use Helmich\TypoScriptLint\Linter\ReportPrinter\AutofixConsoleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\AutofixReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
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
     * @param bool            $tty           Describes if the command output to a terminal or into a file
     * @return LinterLoggerInterface The printer matching the user's specifications.
     */
    public function createLogger(string $outputFormat, OutputInterface $reportOutput, OutputInterface $consoleOutput, bool $tty = true): LinterLoggerInterface
    {
        $isStdout = ($reportOutput instanceof ConsoleOutputInterface);
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
            case 'diff':
                if ($tty) {
                    if ($isStdout) {
                        return new CompactConsoleLogger(new AutofixConsoleReportPrinter($reportOutput), $consoleOutput);
                    } else {
                        return new CompactConsoleLogger(new AutofixReportPrinter($reportOutput), $consoleOutput);
                    }
                } else {
                    return new NullLogger(new AutofixReportPrinter($reportOutput));
                }
            default:
                throw new \InvalidArgumentException('Invalid report printer "' . $outputFormat . '"!');
        }
    }
}
