<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Report;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use PHPUnit\Framework\TestCase;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\assertCount;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\equalTo;
use function PHPUnit\Framework\identicalTo;
use function PHPUnit\Framework\any;

/**
 * Class FileTest
 *
 * @package Helmich\TypoScriptLint\Linter\Report
 * @covers  \Helmich\TypoScriptLint\Linter\Report\File
 */
class FileTest extends TestCase
{

    /** @var File */
    private $file;

    public function setUp(): void
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

        assertCount(1, $this->file->getIssues());
        assertSame($warning, $this->file->getIssues()[0]);
    }

    /**
     * @depends testWarningsCanBeAdded
     */
    public function testWarningsAreSortedByLineNumber()
    {
        $warningBuilder = $this->getMockBuilder(Issue::class)->disableOriginalConstructor();

        $warning1 = $warningBuilder->getMock();
        $warning1->expects(any())->method('getLine')->willReturn(10);
        $warning2 = $warningBuilder->getMock();
        $warning2->expects(any())->method('getLine')->willReturn(1);

        $this->file->addIssue($warning1);
        $this->file->addIssue($warning2);

        assertSame($warning2, $this->file->getIssues()[0]);
        assertSame($warning1, $this->file->getIssues()[1]);
    }

    public function testIssuesCanBeFilteredBySeverity()
    {
        $notice = new Issue(1, 1, "some notice", Issue::SEVERITY_INFO, __CLASS__);
        $warning = new Issue(1, 1, "some warning", Issue::SEVERITY_WARNING, __CLASS__);

        $this->file->addIssue($notice);
        $this->file->addIssue($warning);

        $warnings = $this->file->getIssuesBySeverity(Issue::SEVERITY_WARNING);

        assertCount(1, $warnings);
        assertSame($warnings[0], $warning);
    }
}
