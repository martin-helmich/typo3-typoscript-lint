<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\File;
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
}
