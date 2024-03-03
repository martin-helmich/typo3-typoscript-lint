<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Logging;

use Helmich\TypoScriptLint\Logging\CompactConsoleLogger;
use Helmich\TypoScriptLint\Logging\LinterLoggerBuilder;
use Helmich\TypoScriptLint\Logging\MinimalConsoleLogger;
use Helmich\TypoScriptLint\Logging\VerboseConsoleLogger;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Output\BufferedOutput;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\isInstanceOf;

/**
 * @covers \Helmich\TypoScriptLint\Logging\LinterLoggerBuilder
 */
class LinterLoggerBuilderTest extends TestCase
{
    private LinterLoggerBuilder $builder;

    public function setUp(): void
    {
        $this->builder = new LinterLoggerBuilder();
    }

    public function testCompactLoggerCanBeBuilt(): void
    {
        $logger = $this->builder->createLogger('compact', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, isInstanceOf(CompactConsoleLogger::class));
    }

    public function testVerboseLoggerCanBeBuilt(): void
    {
        $logger = $this->builder->createLogger('text', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, isInstanceOf(VerboseConsoleLogger::class));
    }

    public function testCheckstyleLoggerCanBeBuilt(): void
    {
        $logger = $this->builder->createLogger('xml', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, isInstanceOf(CompactConsoleLogger::class));
    }

    public function testGccLoggerCanBeBuilt(): void
    {
        $logger = $this->builder->createLogger('gcc', new BufferedOutput(), new BufferedOutput());
        assertThat($logger, isInstanceOf(MinimalConsoleLogger::class));
    }

    public function testUnknownFormatCausesInvalidArgumentException(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->builder->createLogger('unknown', new BufferedOutput(), new BufferedOutput());
    }
}
