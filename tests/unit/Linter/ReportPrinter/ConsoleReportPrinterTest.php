<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @covers \Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter
 * @uses   \Helmich\TypoScriptLint\Linter\Report\File
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Report
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Issue
 */
class ConsoleReportPrinterTest extends TestCase
{

    const EXPECTED_XML_DOCUMENT = '
CHECKSTYLE REPORT
=> foobar.tys.
     123 Message #1
     124 Message #2
=> bar.txt.
     412 Message #3

SUMMARY
3 issues in total. (1 errors, 1 warnings, 1 infos)
';

    /** @var BufferedOutput */
    private $output;

    /** @var ConsoleReportPrinter */
    private $printer;

    public function setUp(): void
    {
        $this->output  = new BufferedOutput();
        $this->printer = new ConsoleReportPrinter($this->output);
    }

    /**
     * @medium
     */
    public function testPlaintextReportIsCorrectlyGenerated()
    {
        $file1 = new File('foobar.tys', "");
        $file1->addIssue(new Issue(123, 12, 'Message #1', Issue::SEVERITY_INFO, 'foobar'));
        $file1->addIssue(new Issue(124, 0, 'Message #2', Issue::SEVERITY_WARNING, 'foobar'));

        $file2 = new File('bar.txt', "");
        $file2->addIssue(new Issue(412, 141, 'Message #3', Issue::SEVERITY_ERROR, 'barbaz'));

        $report = new Report();
        $report->addFile($file1);
        $report->addFile($file2);

        $this->printer->writeReport($report);

        $this->assertEquals(self::EXPECTED_XML_DOCUMENT, $this->output->fetch());
    }
}
