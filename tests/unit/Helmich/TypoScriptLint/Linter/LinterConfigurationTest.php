<?php
namespace Helmich\TypoScriptLint\Linter;

use Helmich\TypoScriptLint\Linter\Sniff\DeadCodeSniff;

/**
 * @package Helmich\TypoScriptLint\Linter
 * @covers  Helmich\TypoScriptLint\Linter\LinterConfiguration
 */
class LinterConfigurationTest extends \PHPUnit_Framework_TestCase
{

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
