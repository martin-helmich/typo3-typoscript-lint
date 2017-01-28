<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;

/**
 * @covers \Helmich\TypoScriptLint\Linter\Report\Report
 */
class ReportTest extends \PHPUnit_Framework_TestCase
{

    public function testFilesCanBeAddedToReport()
    {
        $file = $this->getMockBuilder(File::class)->disableOriginalConstructor()->getMock();

        $report = new Report();
        $report->addFile($file);

        $this->assertCount(1, $report->getFiles());
        $this->assertSame($file, $report->getFiles()[0]);
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
