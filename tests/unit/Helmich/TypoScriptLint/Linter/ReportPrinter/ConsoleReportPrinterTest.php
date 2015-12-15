<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Warning;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter
 * @uses   Helmich\TypoScriptLint\Linter\Report\File
 * @uses   Helmich\TypoScriptLint\Linter\Report\Report
 * @uses   Helmich\TypoScriptLint\Linter\Report\Warning
 */
class ConsoleReportPrinterTest extends \PHPUnit_Framework_TestCase
{

    const EXPECTED_XML_DOCUMENT = '
CHECKSTYLE REPORT
=> foobar.tys.
 123 Message #1
 124 Message #2
=> bar.txt.
 412 Message #3

SUMMARY
3 warnings in total.
';

    /** @var \Symfony\Component\Console\Output\BufferedOutput */
    private $output;

    /** @var ConsoleReportPrinter */
    private $printer;

    public function setUp()
    {
        $this->output  = new BufferedOutput();
        $this->printer = new ConsoleReportPrinter($this->output);
    }

    /**
     * @medium
     */
    public function testPlaintextReportIsCorrectlyGenerated()
    {
        $file1 = new File('foobar.tys');
        $file1->addWarning(new Warning(123, 12, 'Message #1', Warning::SEVERITY_INFO, 'foobar'));
        $file1->addWarning(new Warning(124, null, 'Message #2', Warning::SEVERITY_WARNING, 'foobar'));

        $file2 = new File('bar.txt');
        $file2->addWarning(new Warning(412, 141, 'Message #3', Warning::SEVERITY_ERROR, 'barbaz'));

        $report = new Report();
        $report->addFile($file1);
        $report->addFile($file2);

        $this->printer->writeReport($report);

        $this->assertEquals(self::EXPECTED_XML_DOCUMENT, $this->output->fetch());
    }
}