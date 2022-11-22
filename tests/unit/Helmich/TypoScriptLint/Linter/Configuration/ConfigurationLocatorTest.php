<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Configuration;

use Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;

use function PHPUnit\Framework\any;

/**
 * Class ConfigurationLocatorTest
 *
 * @package \Helmich\TypoScriptLint\Linter\Configuration
 * @covers  \Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator
 */
class ConfigurationLocatorTest extends TestCase
{

    /** @var MockObject */
    private $loader, $processor;

    private ConfigurationLocator $locator;

    public function setUp(): void
    {
        $this->loader = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->processor = $this->getMockBuilder(Processor::class)->getMock();

        /** @noinspection PhpParamsInspection */
        $this->locator = new ConfigurationLocator(
            $this->loader,
            $this->processor
        );
    }

    public function testConfigurationIsLoadedAndProcessed(): void
    {
        $distConfig = ['foo' => 'bar'];
        $localConfig = ['bar' => 'baz'];
        $mergedConfig = ['foo' => 'bar', 'bar' => 'baz'];

        $configuration = $this->getMockBuilder(LinterConfiguration::class)->disableOriginalConstructor()->getMock();
        $configuration->expects($this->once())->method('setConfiguration')->with($mergedConfig);

        $this->loader->expects(any())->method('load')->willReturnMap([
            ['typoscript-lint.dist.yml', null, $distConfig],
            ['test.yml', null, $localConfig],
        ]);

        $this->processor
            ->expects($this->once())
            ->method('processConfiguration')
            ->with($this->isInstanceOf(LinterConfiguration::class), [$distConfig, $localConfig])
            ->willReturn($mergedConfig);

        /** @noinspection PhpParamsInspection */
        $loadedConfiguration = $this->locator->loadConfiguration(['test.yml'], $configuration);
        $this->assertSame($loadedConfiguration, $configuration);
    }

    public function testConfigurationIsLoadedAndProcessedWithDefaultConfigFile(): void
    {
        $distConfig = ['foo' => 'bar'];
        $mergedConfig = $distConfig;

        $configuration = $this->getMockBuilder(LinterConfiguration::class)->disableOriginalConstructor()->getMock();
        $configuration->expects($this->once())->method('setConfiguration')->with($mergedConfig);

        $this->loader->expects($this->once())
            ->method('load')
            ->with('typoscript-lint.dist.yml')
            ->willReturn($distConfig);

        $this->processor
            ->expects($this->once())
            ->method('processConfiguration')
            ->with($this->isInstanceOf(LinterConfiguration::class), [$distConfig])
            ->willReturn($mergedConfig);

        /** @noinspection PhpParamsInspection */
        $loadedConfiguration = $this->locator->loadConfiguration([], $configuration);
        $this->assertSame($loadedConfiguration, $configuration);
    }
}
