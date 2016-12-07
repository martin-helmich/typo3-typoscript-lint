<?php
namespace Helmich\TypoScriptLint\Tests\Unit\Linter;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff;
use Helmich\TypoScriptLint\Linter\Sniff\IndentationSniff;
use Symfony\Component\Config\Definition\Processor;

/**
 * @package \Helmich\TypoScriptLint\Linter
 * @covers  \Helmich\TypoScriptLint\Linter\LinterConfiguration
 */
class LinterConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testSniffConfigurationIsMappedToCorrectClasses()
    {
        $this->assertThatInputFileIsMappedToCorrectSniffDatastructure(
            ["sniffs" => [[
                'class' => 'Indentation',
                'parameters' => ['foo' => 'bar']
            ]]],
            [[
                'class' => IndentationSniff::class,
                'parameters' => ['foo' => 'bar'],
                'disabled' => false
            ]]
        );
    }

    public function testExplicitlyEnabledSniffsAreIncluded()
    {
        $this->assertThatInputFileIsMappedToCorrectSniffDatastructure(
            ["sniffs" => [[
                'class' => 'Indentation',
                'parameters' => ['foo' => 'bar'],
                'disabled' => false
            ]]],
            [[
                'class' => IndentationSniff::class,
                'parameters' => ['foo' => 'bar'],
                'disabled' => false
            ]]
        );
    }

    public function testDisabledSniffsAreNotIncluded()
    {
        $this->assertThatInputFileIsMappedToCorrectSniffDatastructure(
            ["sniffs" => [[
                'class' => 'Indentation',
                'parameters' => ['foo' => 'bar'],
                'disabled' => true
            ]]],
            []
        );
    }

    private function assertThatInputFileIsMappedToCorrectSniffDatastructure($configInput, $expectedOutput)
    {
        $config = new LinterConfiguration();

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($config, [$configInput]);

        $config->setConfiguration($processedConfig);

        assertThat($config->getSniffConfigurations(), equalTo($expectedOutput));
    }

    public function testGetSniffConfigurationsReturnsFQCNs()
    {
        $configArray = [
            'sniffs' => [
                'DeadCode' => [
                    'foo' => 'bar',
                ],
            ],
        ];

        $configuration = new LinterConfiguration();
        $configuration->setConfiguration($configArray);

        $sniffConfigs = $configuration->getSniffConfigurations();

        $this->assertInternalType('array', $sniffConfigs);
        $this->assertCount(1, $sniffConfigs);
        $this->assertEquals(DeadCodeSniff::class, $sniffConfigs[0]['class']);
        $this->assertEquals('bar', $sniffConfigs[0]['foo']);
    }

    public function testDisabledSniffsAreSkipped()
    {
        $configArray = [
            'sniffs' => [
                'DeadCode' => [
                    'disabled' => true,
                    'foo' => 'bar',
                ],
            ],
        ];

        $configuration = new LinterConfiguration();
        $configuration->setConfiguration($configArray);

        $sniffConfigs = $configuration->getSniffConfigurations();

        $this->assertInternalType('array', $sniffConfigs);
        $this->assertCount(0, $sniffConfigs);
    }
}
