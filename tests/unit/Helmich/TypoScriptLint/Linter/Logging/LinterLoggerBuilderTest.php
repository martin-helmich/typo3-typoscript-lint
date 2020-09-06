<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Logging;

use Helmich\TypoScriptLint\Logging\CompactConsoleLogger;
use Helmich\TypoScriptLint\Logging\LinterLoggerBuilder;
use Helmich\TypoScriptLint\Logging\MinimalConsoleLogger;
use Helmich\TypoScriptLint\Logging\VerboseConsoleLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;


/**
 * @covers \Helmich\TypoScriptLint\Logging\LinterLoggerBuilder
 */
class LinterLoggerBuilderTest extends TestCase
{
    /** @var LinterLoggerBuilder */
    private $builder;

    public function setUp(): void
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

    public function testGccLoggerCanBeBuilt()
    {
        $logger = $this->builder->createLogger('gcc', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, self::isInstanceOf(MinimalConsoleLogger::class));
    }

    public function testUnknownFormatCausesInvalidArgumentException()
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->createLogger('unknown', new BufferedOutput(), new BufferedOutput());
    }
}
