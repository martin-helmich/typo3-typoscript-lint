<?php
namespace Helmich\TsParser\Linter\Configuration;


use Helmich\TsParser\Linter\LinterConfiguration;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\Config\Loader\DelegatingLoader;
use Symfony\Component\Config\Loader\LoaderResolver;
use Symfony\Component\Console\Output\OutputInterface;

class ConfigurationLocator
{



    /**
     * @param string          $configurationFile
     * @param OutputInterface $output
     * @return \Helmich\TsParser\Linter\LinterConfiguration
     * @throws \Exception
     */
    public function loadConfiguration($configurationFile = NULL, OutputInterface $output)
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