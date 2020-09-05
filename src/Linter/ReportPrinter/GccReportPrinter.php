<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Report printer that generates an error report according to the GNU Coding Standards [1].
 *
 * [1] https://www.gnu.org/prep/standards/html_node/Errors.html
 *
 * @author     Stefan Szymanski <stefan.szymanski@posteo.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\ReportPrinter
 */
class GccReportPrinter implements Printer
{

    /** @var OutputInterface */
    private $output;

    /**
     * Constructs a new GCC report printer.
     *
     * @param OutputInterface $output Output stream to write on. Might be STDOUT or a file.
     */
    public function __construct(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Writes a report in GCC format.
     *
     * @param Report $report The report to print.
     * @return void
     */
    public function writeReport(Report $report): void
    {
        foreach ($report->getFiles() as $file) {
            foreach ($file->getIssues() as $issue) {
                $this->output->writeLn(sprintf(
                    "%s:%d:%d: %s: %s",
                    $file->getFilename(),
                    $issue->getLine() ?: 0,
                    $issue->getColumn() ?: 0,
                    $issue->getSeverity(),
                    $issue->getMessage()
                ));
            }
        }
    }
}
