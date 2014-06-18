<?php
namespace Helmich\TypoScriptLint\Linter\Configuration;


use Helmich\TypoScriptLint\Linter\LinterConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;


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



    /**
     * Loads the linter configuration.
     *
     * @param string $configurationFile The configuration file to load from. This file will be searched in the current
     *                                  working directory and in the tsparse root directory. Contents from the file
     *                                  will also be merged with the tslint.dist.yml file in the tsparse root directory.
     * @return \Helmich\TypoScriptLint\Linter\LinterConfiguration The linter configuration from the given configuration file.
     */
    public function loadConfiguration($configurationFile = NULL)
    {
        $locator           = new FileLocator([getcwd(), TSPARSE_ROOT]);
        $configurationFile = $configurationFile ?: 'tslint.yml';

        $loaderResolver   = new LoaderResolver(array(new YamlConfigurationLoader($locator)));
        $delegatingLoader = new DelegatingLoader($loaderResolver);

        $distConfig  = $delegatingLoader->load('tslint.dist.yml');
        $localConfig = $delegatingLoader->load($configurationFile);

        $configuration = new LinterConfiguration();

        $processor              = new Processor();
        $processedConfiguration = $processor->processConfiguration(
            $configuration,
            [$distConfig, $localConfig]
        );

        $configuration->setConfiguration($processedConfiguration);
        return $configuration;
    }

}