<?php declare(strict_types=1);

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
     * @param Report $report
     *
     * @return void
     */
    public function writeReport(Report $report): void;
}
