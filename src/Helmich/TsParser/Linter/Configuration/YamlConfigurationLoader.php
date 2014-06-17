<?php
namespace Helmich\TsParser\Linter\Configuration;


use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Yaml;


/**
 * Configuration loader for YAML files.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TsParser
 * @subpackage Linter\Configuration
 */
class YamlConfigurationLoader extends FileLoader
{



    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     * @return array
     */
    public function load($resource, $type = NULL)
    {
        $path         = $this->locator->locate($resource);
        $configValues = Yaml::parse($path);

        return $configValues;
    }



    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed  $resource A resource
     * @param string $type     The resource type
     *
     * @return bool    true if this class supports the given resource, false otherwise
     */
    public function supports($resource, $type = NULL)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}