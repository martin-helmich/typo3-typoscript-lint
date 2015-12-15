<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Symfony\Component\Console\Output\OutputInterface;

/**
 * Locates a report printer based on user input.
 *
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\ReportPrinter
 */
class PrinterLocator
{

    /**
     * Finds a suitable printer for printing lint results.
     *
     * @param string                                            $outputFormat The desired output format, as specified by the user, e.g. via
     *                                                                        command-line parameter.
     * @param \Symfony\Component\Console\Output\OutputInterface $output       The output stream (usually STDOUT or a file).
     * @return \Helmich\TypoScriptLint\Linter\ReportPrinter\Printer The printer matching the user's specifications.
     */
    public function createPrinter($outputFormat, OutputInterface $output)
    {
        switch ($outputFormat) {
            case 'checkstyle':
            case 'xml':
                return new CheckstyleReportPrinter($output);
            case 'txt':
            case 'text':
                return new ConsoleReportPrinter($output);
            default:
                throw new \InvalidArgumentException('Invalid report printer "' . $outputFormat . '"!');
        }
    }
}