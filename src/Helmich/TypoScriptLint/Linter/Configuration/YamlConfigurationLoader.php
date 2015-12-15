<?php
namespace Helmich\TypoScriptLint\Linter\Configuration;

use Helmich\TypoScriptLint\Util\Filesystem;
use Symfony\Component\Config\FileLocatorInterface;
use Symfony\Component\Config\Loader\FileLoader;
use Symfony\Component\Yaml\Parser as YamlParser;

/**
 * Configuration loader for YAML files.
 *
 * @author     Martin Helmich <typo3@martin-helmich.de>
 * @license    MIT
 * @package    Helmich\TypoScriptLint
 * @subpackage Linter\Configuration
 */
class YamlConfigurationLoader extends FileLoader
{

    /** @var \Symfony\Component\Yaml\Parser */
    private $yamlParser;

    /** @var \Helmich\TypoScriptLint\Util\Filesystem */
    private $filesystem;

    /**
     * Constructs a new YAML-based configuration loader.
     *
     * @param \Symfony\Component\Config\FileLocatorInterface $locator    The file locator.
     * @param \Symfony\Component\Yaml\Parser                 $yamlParser The YAML parser.
     * @param \Helmich\TypoScriptLint\Util\Filesystem        $filesystem A filesystem interface.
     */
    public function __construct(FileLocatorInterface $locator, YamlParser $yamlParser, Filesystem $filesystem)
    {
        parent::__construct($locator);

        $this->yamlParser = $yamlParser;
        $this->filesystem = $filesystem;
    }

    /**
     * Loads a resource.
     *
     * @param mixed  $resource The resource
     * @param string $type     The resource type
     * @return array
     */
    public function load($resource, $type = null)
    {
        $path         = $this->locator->locate($resource);
        $file         = $this->filesystem->openFile($path);
        $configValues = $this->yamlParser->parse($file->getContents());

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
    public function supports($resource, $type = null)
    {
        return is_string($resource) && 'yml' === pathinfo(
            $resource,
            PATHINFO_EXTENSION
        );
    }
}