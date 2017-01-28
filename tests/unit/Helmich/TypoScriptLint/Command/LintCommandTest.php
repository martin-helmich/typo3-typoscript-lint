<?php
namespace Helmich\TypoScriptLint\Command;

use Helmich\TypoScriptLint\Logging\LinterLoggerBuilder;
use Helmich\TypoScriptLint\Logging\NullLogger;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
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

    /** @var \Helmich\TypoScriptLint\Command\LintCommand */
    private $command;

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private
        $linter,
        $linterConfigurationLocator,
        $finder;

    /** @var ObjectProphecy */
    private $loggerBuilder;

    public function setUp()
    {
        $this->linter                     = $this->getMockBuilder(
            '\Helmich\TypoScriptLint\Linter\LinterInterface'
        )->getMock();
        $this->linterConfigurationLocator = $this
            ->getMockBuilder('\Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator')
            ->disableOriginalConstructor()
            ->getMock();
        $this->finder                     = $this->getMockBuilder('Helmich\\TypoScriptLint\\Util\\Finder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerBuilder = $this->prophesize('Helmich\\TypoScriptLint\\Logging\\LinterLoggerBuilder');

        $this->command = new LintCommand();

        $this->command->injectLinter($this->linter);
        $this->command->injectLinterConfigurationLocator($this->linterConfigurationLocator);
        $this->command->injectLoggerBuilder($this->loggerBuilder->reveal());
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
        $in = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', null],
                ['format', 'txt'],
                ['config', 'config.yml']
            ]
        );
        $in->expects($this->once())->method('getArgument')->with('paths')->willReturn(['foo.ts']);

        $out = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $this->runCommand($in, $out);
    }

    public function testCommandCallsLinterWithCorrectDependencies()
    {
        $in = $this->getMock('Symfony\Component\Console\Input\InputInterface');
        $in->expects($this->any())->method('getArgument')->with('paths')->willReturn(['foo.ts']);
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', '-'],
                ['format', 'txt'],
                ['config', 'config.yml']
            ]
        );

        $config  = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\LinterConfiguration')->disableOriginalConstructor()->getMock();
        $config->expects(any())->method('getFilePatterns')->willReturn([]);

        $logger = new NullLogger();

        $out = $this->getMock('Symfony\Component\Console\Output\OutputInterface');

        $this->linterConfigurationLocator->expects($this->once())->method('loadConfiguration')->with(
            'config.yml'
        )->willReturn($config);
        $this->loggerBuilder->createLogger('txt', Argument::exact($out), Argument::exact($out))->shouldBeCalled()->willReturn($logger);
        $this->finder->expects($this->once())->method('getFilenames')->willReturnArgument(0);

        $this->runCommand($in, $out);
    }
}
