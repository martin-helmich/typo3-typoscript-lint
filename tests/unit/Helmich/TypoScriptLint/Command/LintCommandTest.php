<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Command;

use Helmich\TypoScriptLint\Command\LintCommand;
use Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\LinterInterface;
use Helmich\TypoScriptLint\Linter\ReportPrinter\Printer;
use Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator;
use Helmich\TypoScriptLint\Util\Finder;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class LintCommandTest
 *
 * @package Helmich\TypoScriptLint\Command
 * @covers  \Helmich\TypoScriptLint\Command\LintCommand
 */
class LintCommandTest extends \PHPUnit_Framework_TestCase
{

    /** @var LintCommand */
    private $command;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private
        $linter,
        $linterConfigurationLocator,
        $printerLocator,
        $finder;

    public function setUp()
    {
        $this->linter                     = $this->getMockBuilder(LinterInterface::class)->getMock();
        $this->linterConfigurationLocator = $this
            ->getMockBuilder(ConfigurationLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->printerLocator             = $this->getMockBuilder(PrinterLocator::class)->getMock();
        $this->finder                     = $this->getMockBuilder(Finder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->command = new LintCommand();

        $this->command->injectLinter($this->linter);
        $this->command->injectLinterConfigurationLocator($this->linterConfigurationLocator);
        $this->command->injectReportPrinterLocator($this->printerLocator);
        $this->command->injectFinder($this->finder);
    }

    private function runCommand(InputInterface $in, OutputInterface $out)
    {
        $class = new \ReflectionClass($this->command);

        $method = $class->getMethod('execute');
        $method->setAccessible(true);
        $method->invoke($this->command, $in, $out);
    }

    /**
     * @expectedException \Helmich\TypoScriptLint\Exception\BadOutputFileException
     */
    public function testCommandThrowsExceptionWhenBadOutputFileIsGiven()
    {
        $in = $this->createMock(InputInterface::class);
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', null],
                ['format', 'txt'],
                ['config', 'config.yml']
            ]
        );
        $in->expects($this->once())->method('getArgument')->with('filename')->willReturn(['foo.ts']);

        $out = $this->createMock(OutputInterface::class);

        $this->runCommand($in, $out);
    }

    public function testCommandCallsLinterWithCorrectDependencies()
    {
        $in = $this->createMock(InputInterface::class);
        $in->expects($this->any())->method('getArgument')->with('filename')->willReturn(['foo.ts']);
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', '-'],
                ['format', 'txt'],
                ['config', 'config.yml']
            ]
        );

        $config  = $this
            ->getMockBuilder(LinterConfiguration::class)
            ->disableOriginalConstructor()
            ->getMock();
        $printer = $this->getMockBuilder(Printer::class)->getMock();

        $out = $this->createMock(OutputInterface::class);

        $this->linterConfigurationLocator
            ->expects($this->once())
            ->method('loadConfiguration')
            ->with('config.yml')->willReturn($config);
        $this->printerLocator
            ->expects($this->once())
            ->method('createPrinter')
            ->with('txt', $this->identicalTo($out))
            ->willReturn($printer);
        $this->finder
            ->expects($this->once())
            ->method('getFilenames')
            ->willReturnArgument(0);

        $this->runCommand($in, $out);
    }
}
