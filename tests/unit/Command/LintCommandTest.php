<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Command;

use Helmich\TypoScriptLint\Command\LintCommand;
use Helmich\TypoScriptLint\Exception\BadOutputFileException;
use Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\LinterInterface;
use Helmich\TypoScriptLint\Linter\Report\File;
use Helmich\TypoScriptLint\Logging\LinterLoggerBuilder;
use Helmich\TypoScriptLint\Logging\LinterLoggerInterface;
use Helmich\TypoScriptLint\Logging\NullLogger;
use Helmich\TypoScriptLint\Util\Finder;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

/**
 * Class LintCommandTest
 *
 * @package Helmich\TypoScriptLint\Command
 * @covers  \Helmich\TypoScriptLint\Command\LintCommand
 */
class LintCommandTest extends TestCase
{

    /** @var LintCommand */
    private $command;

    /** @var MockObject */
    private
        $linter,
        $linterConfigurationLocator,
        $finder;

    /** @var ObjectProphecy */
    private $loggerBuilder, $eventDispatcher;

    public function setUp(): void
    {
        $this->linter = $this->getMockBuilder(LinterInterface::class)->getMock();
        $this->linter->expects(any())->method('lintFile')->willReturn(new File('foo.ts', ""));

        $this->linterConfigurationLocator = $this
            ->getMockBuilder(ConfigurationLocator::class)
            ->disableOriginalConstructor()
            ->getMock();
        $this->finder = $this
            ->getMockBuilder(Finder::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->loggerBuilder = $this->prophesize(LinterLoggerBuilder::class);
        $this->eventDispatcher = $this->prophesize(EventDispatcher::class);

        $this->command = new LintCommand(
            $this->linter,
            $this->linterConfigurationLocator,
            $this->loggerBuilder->reveal(),
            $this->finder,
            $this->eventDispatcher->reveal()
        );
    }

    private function runCommand(InputInterface $in, OutputInterface $out)
    {
        $class = new \ReflectionClass($this->command);

        $method = $class->getMethod('execute');
        $method->setAccessible(true);
        $method->invoke($this->command, $in, $out);
    }

    public function testCommandThrowsExceptionWhenBadOutputFileIsGiven()
    {
        $this->expectException(BadOutputFileException::class);

        $in = $this->createMock(InputInterface::class);
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', null],
                ['format', 'txt'],
                ['config', 'config.yml'],
            ]
        );
        $in->expects($this->once())->method('getArgument')->with('paths')->willReturn(['foo.ts']);

        $out = $this->createMock(OutputInterface::class);

        $this->runCommand($in, $out);
    }

    public function testCommandCallsLinterWithCorrectDependencies()
    {
        $in = $this->createMock(InputInterface::class);
        $in->expects($this->any())->method('getArgument')->with('paths')->willReturn(['foo.ts']);
        $in->expects($this->any())->method('getOption')->willReturnMap(
            [
                ['output', '-'],
                ['format', 'txt'],
                ['config', 'config.yml'],
            ]
        );

        $config = $this->getMockBuilder(LinterConfiguration::class)->disableOriginalConstructor()->getMock();
        $config->expects(any())->method('getFilePatterns')->willReturn([]);

        $logger = new NullLogger();

        $out = $this->createMock(OutputInterface::class);

        $this->linterConfigurationLocator
            ->expects(once())
            ->method('loadConfiguration')
            ->with(['config.yml'])
            ->willReturn($config);

        $this->loggerBuilder->createLogger('txt', Argument::exact($out), Argument::exact($out), Argument::any())->shouldBeCalled()->willReturn($logger);
        $this->finder->expects($this->once())->method('getFilenames')->willReturnArgument(0);

        $this->runCommand($in, $out);
    }
}
