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
        $this->loader = $loader;
        $this->processor = $processor;
    }

    /**
     * Loads the linter configuration.
     *
     * @param string[]            $possibleConfigurationFiles A list of possible configuration files to load from. These
     *                                                        files will be searched in the current working directory
     *                                                        and in the typoscript-lint root directory. Contents from
     *                                                        these files will also be merged with the
     *                                                        typoscript-lint.dist.yml file in the typoscript-lint root
     *                                                        directory.
     * @param LinterConfiguration $configuration              The configuration on which to set the loaded configuration values.
     * @return LinterConfiguration The linter configuration from the given configuration file.
     */
    public function loadConfiguration($possibleConfigurationFiles = [], LinterConfiguration $configuration = null)
    {
        $configs = [$this->loader->load('typoscript-lint.dist.yml')];
        foreach ($possibleConfigurationFiles as $configurationFile) {
            $loadedConfig = $this->loader->load($configurationFile);

            // Simple mechanism to detect tslint config files ("ts" as in "TypeScript", not "Typoscript")
            // and excluding them from being loaded.
            if (isset($loadedConfig["extends"]) || isset($loadedConfig["rulesDirectory"]) || isset($loadedConfig["rules"])) {
                continue;
            }

            $configs[] = $loadedConfig;
        }

        $configuration = $configuration ?: new LinterConfiguration();

        $processedConfiguration = $this->processor->processConfiguration(
            $configuration,
            $configs
        );

        $configuration->setConfiguration($processedConfiguration);
        return $configuration;
    }
}
