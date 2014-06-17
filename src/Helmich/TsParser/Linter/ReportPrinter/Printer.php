<?php
namespace Helmich\TsParser\Linter\ReportPrinter;


use Helmich\TsParser\Linter\Report\Report;


/**
 * Interface definition for code linting report printers.
 *
 * @package    Helmich\TsParser
 * @subpcakage Linter\ReportPrinter
 */
interface Printer
{



    /**
     * Writes a report.
     *
     * @param \Helmich\TsParser\Linter\Report\Report $report
     * @return void
     */
    public function writeReport(Report $report);


}