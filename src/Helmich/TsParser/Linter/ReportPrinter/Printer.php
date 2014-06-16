<?php
namespace Helmich\TsParser\Linter\ReportPrinter;


use Helmich\TsParser\Linter\Report\Report;


interface Printer
{



    public function writeReport(Report $report);

}