<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;

/**
 * Class FileTest
 *
 * @package Helmich\TypoScriptLint\Linter\Report
 * @covers  \Helmich\TypoScriptLint\Linter\Report\File
 */
class FileTest extends \PHPUnit_Framework_TestCase
{

    /** @var File */
    private $file;

    public function setUp()
    {
        $this->file = new File('test.tys');
    }

    public function testConstructorSetsFilename()
    {
        $this->assertEquals('test.tys', $this->file->getFilename());
    }

    public function testWarningsCanBeAdded()
    {
        $warning = $this->getMockBuilder(Issue::class)->disableOriginalConstructor()->getMock();
        $this->file->addIssue($warning);

        $this->assertCount(1, $this->file->getIssues());
        $this->assertSame($warning, $this->file->getIssues()[0]);
    }

    /**
     * @depends testWarningsCanBeAdded
     */
    public function testWarningsAreSortedByLineNumber()
    {
        $warningBuilder = $this->getMockBuilder(Issue::class)->disableOriginalConstructor();

        $warning1 = $warningBuilder->getMock();
        $warning1->expects($this->any())->method('getLine')->willReturn(10);
        $warning2 = $warningBuilder->getMock();
        $warning2->expects($this->any())->method('getLine')->willReturn(1);

        $this->file->addIssue($warning1);
        $this->file->addIssue($warning2);

        $this->assertSame($warning2, $this->file->getIssues()[0]);
        $this->assertSame($warning1, $this->file->getIssues()[1]);
    }

    public function testIssuesCanBeFilteredBySeverity()
    {
        $notice = new Issue(1, 1, "some notice", Issue::SEVERITY_INFO, __CLASS__);
        $warning = new Issue(1, 1, "some warning", Issue::SEVERITY_WARNING, __CLASS__);

        $this->file->addIssue($notice);
        $this->file->addIssue($warning);

        $warnings = $this->file->getIssuesBySeverity(Issue::SEVERITY_WARNING);

        assertThat(count($warnings), equalTo(1));
        assertThat($warnings[0], identicalTo($warning));
    }
}
