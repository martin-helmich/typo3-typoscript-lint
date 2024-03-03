<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\ReportPrinter\GccReportPrinter;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

use function PHPUnit\Framework\assertEquals;

/**
 * @covers \Helmich\TypoScriptLint\Linter\ReportPrinter\GccReportPrinter
 * @uses   \Helmich\TypoScriptLint\Linter\Report\File
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Report
 * @uses   \Helmich\TypoScriptLint\Linter\Report\Issue
 */
class GccReportPrinterTest extends TestCase
{

    public const EXPECTED_OUTPUT = 'foobar.tys:123:12: info: Message #1
foobar.tys:124:0: warning: Message #2
bar.txt:412:141: error: Message #3
';

    private BufferedOutput $output;

    private GccReportPrinter $printer;

    public function setUp(): void
    {
        $this->output = new BufferedOutput();
        $this->printer = new GccReportPrinter($this->output);
    }

    /**
     * @medium
     */
    public function testGccReportIsCorrectlyGenerated(): void
    {
        $file1 = new File('foobar.tys');
        $file1->addIssue(new Issue(123, 12, 'Message #1', Issue::SEVERITY_INFO, 'foobar'));
        $file1->addIssue(new Issue(124, 0, 'Message #2', Issue::SEVERITY_WARNING, 'foobar'));

        $file2 = new File('bar.txt');
        $file2->addIssue(new Issue(412, 141, 'Message #3', Issue::SEVERITY_ERROR, 'barbaz'));

        $report = new Report();
        $report->addFile($file1);
        $report->addFile($file2);

        $this->printer->writeReport($report);

        assertEquals(self::EXPECTED_OUTPUT, $this->output->fetch());
    }
}
