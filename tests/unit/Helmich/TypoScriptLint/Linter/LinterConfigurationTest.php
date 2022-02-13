<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Tests\Unit\Linter;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Config\Definition\Processor;

use function PHPUnit\Framework\assertThat;
use function PHPUnit\Framework\equalTo;

/**
 * @package Helmich\TypoScriptLint\Linter
 * @covers  \Helmich\TypoScriptLint\Linter\LinterConfiguration
 */
class LinterConfigurationTest extends TestCase
{
    public function testPathsAreCorrectlyMapped()
    {
        $config = new LinterConfiguration();

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($config, [[
            'paths' => ['./foo', './bar', './baz'],
            'sniffs' => []
        ]]);

        $config->setConfiguration($processedConfig);

        assertThat($config->getPaths(), equalTo(['./foo', './bar', './baz']));
    }

    public function testFileExtensionsAreEmptyByDefault()
    {
        $config = new LinterConfiguration();

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($config, [[
            'sniffs' => []
        ]]);

        $config->setConfiguration($processedConfig);

        assertThat($config->getFilePatterns(), equalTo([]));
    }

    public function testFileExtensionsFromInputfileAreCorrectlyMapped()
    {
        $config = new LinterConfiguration();

        $processor = new Processor();
        $processedConfig = $processor->processConfiguration($config, [[
            'filePatterns' => [
                '*.ts',
                '*.typoscript'
            ],
            'sniffs' => []
        ]]);

        $config->setConfiguration($processedConfig);

        assertThat($config->getFilePatterns(), equalTo(['*.ts', '*.typoscript']));
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

        $this->assertIsArray($sniffConfigs);
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

        $this->assertIsArray($sniffConfigs);
        $this->assertCount(0, $sniffConfigs);
    }
}
