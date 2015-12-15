<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;

/**
 * Interface definition for code linting report printers.
 *
 * @package    Helmich\TypoScriptLint
 * @subpcakage Linter\ReportPrinter
 */
interface Printer
{

    /**
     * Writes a report.
     *
     * @param \Helmich\TypoScriptLint\Linter\Report\Report $report
     * @return void
     */
    public function writeReport(Report $report);
}
