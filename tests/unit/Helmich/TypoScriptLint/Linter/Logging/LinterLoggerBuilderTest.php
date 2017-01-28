<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Logging;

use Helmich\TypoScriptLint\Logging\CompactConsoleLogger;
use Helmich\TypoScriptLint\Logging\LinterLoggerBuilder;
use Helmich\TypoScriptLint\Logging\VerboseConsoleLogger;
use Symfony\Component\Console\Output\BufferedOutput;


/**
 * @covers \Helmich\TypoScriptLint\Logging\LinterLoggerBuilder
 */
class LinterLoggerBuilderTest extends \PHPUnit_Framework_TestCase
{
    /** @var LinterLoggerBuilder */
    private $builder;

    public function setUp()
    {
        $this->builder = new LinterLoggerBuilder();
    }

    public function testCompactLoggerCanBeBuilt()
    {
        $logger = $this->builder->createLogger('compact', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, self::isInstanceOf(CompactConsoleLogger::class));
    }

    public function testVerboseLoggerCanBeBuilt()
    {
        $logger = $this->builder->createLogger('text', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, self::isInstanceOf(VerboseConsoleLogger::class));
    }

    public function testCheckstyleLoggerCanBeBuilt()
    {
        $logger = $this->builder->createLogger('xml', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, self::isInstanceOf(CompactConsoleLogger::class));
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testUnknownFormatCausesInvalidArgumentException()
    {
        $this->builder->createLogger('unknown', new BufferedOutput(), new BufferedOutput());
    }
}