<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\Report;

/**
 * Interface definition for code linting report printers.
 *
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\ReportPrinter
 */
interface Printer
{

    public function writeReport(Report $report): void;
}
