<?php
namespace Helmich\TypoScriptLint\Linter\Configuration;

use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Loader\LoaderInterface;

/**
 * Helper class that loads linting configuration data.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\Configuration
 */
class ConfigurationLocator
{

    /** @var LoaderInterface */
    private $loader;

    /** @var Processor */
    private $processor;

    /**
     * Constructs a new configuration locator.
     *
     * @param LoaderInterface $loader    A configuration loader.
     * @param Processor       $processor A configuration processor.
     */
    public function __construct(LoaderInterface $loader, Processor $processor)
    {
        $this->loader    = $loader;
        $this->processor = $processor;
    }

    /**
     * Loads the linter configuration.
     *
     * @param string              $configurationFile The configuration file to load from. This file will be searched in
     *                                               the current working directory and in the tslint root directory.
     *                                               Contents from the file will also be merged with the tslint.dist.yml
     *                                               file in the tslint root directory.
     * @param LinterConfiguration $configuration     The configuration on which to set the loaded configuration values.
     * @return LinterConfiguration The linter configuration from the given configuration file.
     */
    public function loadConfiguration($configurationFile = null, LinterConfiguration $configuration = null)
    {
        $distConfig  = $this->loader->load('tslint.dist.yml');
        $localConfig = $configurationFile ? $this->loader->load($configurationFile) : [];

        $configuration = $configuration ?: new LinterConfiguration();

        $processedConfiguration = $this->processor->processConfiguration(
            $configuration,
            [$distConfig, $localConfig]
        );

        $configuration->setConfiguration($processedConfiguration);
        return $configuration;
    }
}
