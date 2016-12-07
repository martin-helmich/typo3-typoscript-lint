<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter;
use Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator;
use Symfony\Component\Console\Output\NullOutput;

/**
 * @covers \Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator
 * @uses   \Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter
 * @uses   \Helmich\TypoScriptLint\Linter\ReportPrinter\ConsoleReportPrinter
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
        $this->assertInstanceOf(CheckstyleReportPrinter::class, $this->locator->createPrinter('xml', new NullOutput()));
    }

    public function testConsolePrinterIsCreated()
    {
        $this->assertInstanceOf(ConsoleReportPrinter::class, $this->locator->createPrinter('text', new NullOutput()));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testInvalidArgumentExceptionIsThrownOnUnknownFormat()
    {
        $this->locator->createPrinter('pdf', new NullOutput());
    }
}
