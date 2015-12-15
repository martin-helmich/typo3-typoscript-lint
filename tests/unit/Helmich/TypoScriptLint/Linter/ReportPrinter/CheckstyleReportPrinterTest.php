<?php
namespace Helmich\TypoScriptLint\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\Report\Warning;

/**
 * @covers Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter
 * @uses   Helmich\TypoScriptLint\Linter\Report\File
 * @uses   Helmich\TypoScriptLint\Linter\Report\Report
 * @uses   Helmich\TypoScriptLint\Linter\Report\Warning
 */
class CheckstyleReportPrinterTest extends \PHPUnit_Framework_TestCase
{

    const EXPECTED_XML_DOCUMENT = '<?xml version="1.0" encoding="UTF-8"?>
<checkstyle version="typoscript-lint-1.0.0">
  <file name="foobar.tys">
    <error line="123" severity="info" message="Message #1" source="foobar" column="12"/>
    <error line="124" severity="warning" message="Message #2" source="foobar"/>
  </file>
  <file name="bar.txt">
    <error line="412" severity="error" message="Message #3" source="barbaz" column="141"/>
  </file>
</checkstyle>
';

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $output;

    /** @var CheckstyleReportPrinter */
    private $printer;

    public function setUp()
    {
        $this->output  = $this->getMockBuilder('Symfony\Component\Console\Output\OutputInterface')->getMock();
        $this->printer = new CheckstyleReportPrinter($this->output);

        define('APP_NAME', 'typoscript-lint');
        define('APP_VERSION', '1.0.0');
    }

    /**
     * @medium
     */
    public function testXmlReportIsCorrectlyGenerated()
    {
        $file1 = new File('foobar.tys');
        $file1->addWarning(new Warning(123, 12, 'Message #1', Warning::SEVERITY_INFO, 'foobar'));
        $file1->addWarning(new Warning(124, null, 'Message #2', Warning::SEVERITY_WARNING, 'foobar'));

        $file2 = new File('bar.txt');
        $file2->addWarning(new Warning(412, 141, 'Message #3', Warning::SEVERITY_ERROR, 'barbaz'));

        $report = new Report();
        $report->addFile($file1);
        $report->addFile($file2);

        $this->output->expects($this->once())->method('write')->with(self::EXPECTED_XML_DOCUMENT);

        $this->printer->writeReport($report);
    }
}
