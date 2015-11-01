<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Symfony\Component\Console\Output\NullOutput;


/**
 * @covers Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator
 * @uses   Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter
 * @uses   Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter
 */
class PrinterLocatorTest extends \PHPUnit_Framework_TestCase
{



    /** @var PrinterLocator */
    private $locator;



    public function setUp()
    {
        $this->locator = new PrinterLocator();
    }



    public function testCheckstylePrinterIsCreated()
    {
        $this->assertInstanceOf(
            'Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter',
            $this->locator->createPrinter('xml', new NullOutput())
        );
    }



    public function testConsolePrinterIsCreated()
    {
        $this->assertInstanceOf(
            'Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter',
            $this->locator->createPrinter('text', new NullOutput())
        );
    }



    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentExceptionIsThrownOnUnknownFormat()
    {
        $this->locator->createPrinter('pdf', new NullOutput());
    }

}