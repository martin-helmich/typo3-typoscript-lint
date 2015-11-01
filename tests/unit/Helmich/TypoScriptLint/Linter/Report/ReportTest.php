<?php
namespace Helmich\TypoScriptLint\Linter\Report;


/**
 * @covers Helmich\TypoScriptLint\Linter\Report\Report
 */
class ReportTest extends \PHPUnit_Framework_TestCase
{



    public function testFilesCanBeAddedToReport()
    {
        $file   = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\Report\File')->disableOriginalConstructor()->getMock();

        $report = new Report();
        $report->addFile($file);

        $this->assertCount(1, $report->getFiles());
        $this->assertSame($file, $report->getFiles()[0]);
    }

}