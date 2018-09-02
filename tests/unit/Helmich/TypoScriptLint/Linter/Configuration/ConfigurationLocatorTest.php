<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter\Configuration;

use Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator;
use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Class ConfigurationLocatorTest
 *
 * @package \Helmich\TypoScriptLint\Linter\Configuration
 * @covers  \Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator
 */
class ConfigurationLocatorTest extends \PHPUnit_Framework_TestCase
{

    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $loader, $processor;

    /** @var ConfigurationLocator */
    private $locator;

    public function setUp()
    {
        $this->loader    = $this->getMockBuilder(LoaderInterface::class)->getMock();
        $this->processor = $this->getMockBuilder(Processor::class)->getMock();

        /** @noinspection PhpParamsInspection */
        $this->locator = new ConfigurationLocator(
            $this->loader,
            $this->processor
        );
    }

    public function testConfigurationIsLoadedAndProcessed()
    {
        $distConfig   = ['foo' => 'bar'];
        $localConfig  = ['bar' => 'baz'];
        $mergedConfig = ['foo' => 'bar', 'bar' => 'baz'];

        $configuration = $this->getMockBuilder(LinterConfiguration::class)->disableOriginalConstructor()->getMock();
        $configuration->expects($this->once())->method('setConfiguration')->with($mergedConfig);

        $this->loader->expects($this->at(0))->method('load')->with('typoscript-lint.dist.yml')->willReturn($distConfig);
        $this->loader->expects($this->at(1))->method('load')->with('test.yml')->willReturn($localConfig);

        $this->processor
            ->expects($this->once())
            ->method('processConfiguration')
            ->with($this->isInstanceOf(LinterConfiguration::class), [$distConfig, $localConfig])
            ->willReturn($mergedConfig);

        /** @noinspection PhpParamsInspection */
        $loadedConfiguration = $this->locator->loadConfiguration(['test.yml'], $configuration);
        $this->assertSame($loadedConfiguration, $configuration);
    }

    public function testConfigurationIsLoadedAndProcessedWithDefaultConfigFile()
    {
        $distConfig   = ['foo' => 'bar'];
        $mergedConfig = $distConfig;

        $configuration = $this->getMockBuilder(LinterConfiguration::class)->disableOriginalConstructor()->getMock();
        $configuration->expects($this->once())->method('setConfiguration')->with($mergedConfig);

        $this->loader->expects($this->once())->method('load')->with('typoscript-lint.dist.yml')->willReturn($distConfig);

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
