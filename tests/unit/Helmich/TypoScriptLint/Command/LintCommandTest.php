<?php
namespace Helmich\TypoScriptLint\Command;


use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class LintCommandTest
 * @package Helmich\TypoScriptLint\Command
 * @covers  \Helmich\TypoScriptLint\Command\LintCommand
 */
class LintCommandTest extends \PHPUnit_Framework_TestCase
{



    /** @var \Helmich\TypoScriptLint\Command\LintCommand */
    private $command;


    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private
        $linter,
        $linterConfigurationLocator,
        $printerLocator,
        $finder;



    public function setUp()
    {
        $this->linter                     = $this->getMockBuilder('\Helmich\TypoScriptLint\Linter\LinterInterface')->getMock();
        $this->linterConfigurationLocator = $this
            ->getMockBuilder('\Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->printerLocator             = $this->getMockBuilder('\Helmich\TypoScriptLint\Linter\ReportPrinter\PrinterLocator')->getMock();
        $this->finder                     = $this->getMockBuilder('Helmich\\TypoScriptLint\\Util\\Finder')
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
        $method->setAccessible(TRUE);
        $method->invoke($this->command, $in, $out);
    }



    /**
     * @expectedException \Helmich\TypoScriptLint\Exception\BadOutputFileException
     */
    public function testCommandThrowsExceptionWhenBadOutputFileIsGiven()
    {
        $in = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', NULL],
                ['format', 'txt'],
                ['config', 'config.yml']
            ]
        );
        $in->expects($this->once())->method('getArgument')->with('filename')->willReturn(['foo.ts']);

        $out = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $this->runCommand($in, $out);
    }



    public function testCommandCallsLinterWithCorrectDependencies()
    {
        $in = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $in->expects($this->any())->method('getArgument')->with('filename')->willReturn(['foo.ts']);
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', '-'],
                ['format', 'txt'],
                ['config', 'config.yml']
            ]
        );

        $config  = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\LinterConfiguration')->disableOriginalConstructor()->getMock();
        $printer = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\ReportPrinter\Printer')->getMock();

        $out = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $this->linterConfigurationLocator->expects($this->once())->method('loadConfiguration')->with('config.yml')->willReturn($config);
        $this->printerLocator->expects($this->once())->method('createPrinter')->with('txt', $this->identicalTo($out))->willReturn($printer);
        $this->finder->expects($this->once())->method('getFilenames')->willReturnArgument(0);

        $this->runCommand($in, $out);
    }

}