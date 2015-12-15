<?php
namespace Helmich\TypoScriptLint\Linter\Report;

/**
 * Class FileTest
 *
 * @package Helmich\TypoScriptLint\Linter\Report
 * @covers  Helmich\TypoScriptLint\Linter\Report\File
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
        $warning = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\Report\Warning')->disableOriginalConstructor(
        )->getMock();
        $this->file->addWarning($warning);

        $this->assertCount(1, $this->file->getWarnings());
        $this->assertSame($warning, $this->file->getWarnings()[0]);
    }

    /**
     * @depends testWarningsCanBeAdded
     */
    public function testWarningsAreSortedByLineNumber()
    {
        $warningBuilder = $this->getMockBuilder(
            'Helmich\TypoScriptLint\Linter\Report\Warning'
        )->disableOriginalConstructor();

        $warning1 = $warningBuilder->getMock();
        $warning1->expects($this->any())->method('getLine')->willReturn(10);
        $warning2 = $warningBuilder->getMock();
        $warning2->expects($this->any())->method('getLine')->willReturn(1);

        $this->file->addWarning($warning1);
        $this->file->addWarning($warning2);

        $this->assertSame($warning2, $this->file->getWarnings()[0]);
        $this->assertSame($warning1, $this->file->getWarnings()[1]);
    }
}