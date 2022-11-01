<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\equalTo;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Report\Report
 */
class ReportTest extends TestCase
{

    public function testFilesCanBeAddedToReport()
    {
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();

        $report = new Report();
        $report->addFile($file);

        assertCount(1, $report->getFiles());
        assertSame($file, $report->getFiles()[0]);
    }

    public function testCanCountIssues()
    {
        $report = $this->buildSampleReport();
        assertThat($report->countIssues(), equalTo(3));
    }

    public function testCanCountIssuesBySeverity()
    {
        $report = $this->buildSampleReport();
        assertThat($report->countIssuesBySeverity(Issue::SEVERITY_WARNING), equalTo(2));
    }

    /**
     * @return Report
     */
    private function buildSampleReport()
    {
        $file1 = new File("foo");
        $file1->addIssue(new Issue(1, 1, "foo", Issue::SEVERITY_WARNING, __CLASS__));

        $file2 = new File("bar");
        $file2->addIssue(new Issue(1, 1, "foo", Issue::SEVERITY_WARNING, __CLASS__));
        $file2->addIssue(new Issue(1, 1, "foo", Issue::SEVERITY_INFO, __CLASS__));

        $report = new Report();
        $report->addFile($file1);
        $report->addFile($file2);
        return $report;
    }
}
