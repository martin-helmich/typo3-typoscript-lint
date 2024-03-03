<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\ReportPrinter;

use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Linter\Report\Issue;
use Helmich\TypoScriptLint\Linter\Report\Report;
use Helmich\TypoScriptLint\Linter\ReportPrinter\CheckstyleReportPrinter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\OutputInterface;

use function PHPUnit\Framework\once;

#[CoversClass(CheckstyleReportPrinter::class)]
class CheckstyleReportPrinterTest extends TestCase
{

    public const EXPECTED_XML_DOCUMENT = '<?xml version="1.0" encoding="UTF-8"?>
<checkstyle version="typoscript-lint-dev">
  <file name="foobar.tys">
    <error line="123" severity="info" message="Message #1" source="foobar" column="12"/>
    <error line="124" severity="warning" message="Message #2" source="foobar"/>
  </file>
  <file name="bar.txt">
    <error line="412" severity="error" message="Message #3" source="barbaz" column="141"/>
  </file>
</checkstyle>
';

    /** @var OutputInterface&MockObject */
    private OutputInterface $output;

    private CheckstyleReportPrinter $printer;

    public function setUp(): void
    {
        $this->output = $this->getMockBuilder(OutputInterface::class)->getMock();
        $this->printer = new CheckstyleReportPrinter($this->output);
    }

    /**
     * @medium
     */
    public function testXmlReportIsCorrectlyGenerated(): void
    {
        $file1 = new File('foobar.tys');
        $file1->addIssue(new Issue(123, 12, 'Message #1', Issue::SEVERITY_INFO, 'foobar'));
        $file1->addIssue(new Issue(124, null, 'Message #2', Issue::SEVERITY_WARNING, 'foobar'));

        $file2 = new File('bar.txt');
        $file2->addIssue(new Issue(412, 141, 'Message #3', Issue::SEVERITY_ERROR, 'barbaz'));

        $report = new Report();
        $report->addFile($file1);
        $report->addFile($file2);

        $this->output->expects(once())->method('write')->with(self::EXPECTED_XML_DOCUMENT);

        $this->printer->writeReport($report);
    }
}
