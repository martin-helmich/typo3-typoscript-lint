<?php
namespace Helmich\TypoScriptLint\Linter\Configuration;


/**
 * Class ConfigurationLocatorTest
 *
 * @package Helmich\TypoScriptLint\Linter\Configuration
 * @covers  Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator
 */
class ConfigurationLocatorTest extends \PHPUnit_Framework_TestCase
{



    /** @var \PHPUnit_Framework_MockObject_MockObject */
    private $loader, $processor;


    /** @var \Helmich\TypoScriptLint\Linter\Configuration\ConfigurationLocator */
    private $locator;



    public function setUp()
    {
        $this->loader    = $this->getMockBuilder('Symfony\Component\Config\Loader\LoaderInterface')->getMock();
        $this->processor = $this->getMockBuilder('Symfony\Component\Config\Definition\Processor')->getMock();

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

        $configuration = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\LinterConfiguration')->disableOriginalConstructor()->getMock(
        );
        $configuration->expects($this->once())->method('setConfiguration')->with($mergedConfig);

        $this->loader->expects($this->at(0))->method('load')->with('tslint.dist.yml')->willReturn($distConfig);
        $this->loader->expects($this->at(1))->method('load')->with('test.yml')->willReturn($localConfig);

        $this->processor
            ->expects($this->once())
            ->method('processConfiguration')
            ->with($this->isInstanceOf('Helmich\TypoScriptLint\Linter\LinterConfiguration'), [$distConfig, $localConfig])
            ->willReturn($mergedConfig);

        /** @noinspection PhpParamsInspection */
        $loadedConfiguration = $this->locator->loadConfiguration('test.yml', $configuration);
        $this->assertSame($loadedConfiguration, $configuration);
    }



    public function testConfigurationIsLoadedAndProcessedWithDefaultConfigFile()
    {
        $distConfig   = ['foo' => 'bar'];
        $mergedConfig = $distConfig;

        $configuration = $this->getMockBuilder('Helmich\TypoScriptLint\Linter\LinterConfiguration')->disableOriginalConstructor()->getMock(
        );
        $configuration->expects($this->once())->method('setConfiguration')->with($mergedConfig);

        $this->loader->expects($this->once())->method('load')->with('tslint.dist.yml')->willReturn($distConfig);

        $this->processor
            ->expects($this->once())
            ->method('processConfiguration')
            ->with($this->isInstanceOf('Helmich\TypoScriptLint\Linter\LinterConfiguration'), [$distConfig, []])
            ->willReturn($mergedConfig);

        /** @noinspection PhpParamsInspection */
        $loadedConfiguration = $this->locator->loadConfiguration(NULL, $configuration);
        $this->assertSame($loadedConfiguration, $configuration);
    }

}