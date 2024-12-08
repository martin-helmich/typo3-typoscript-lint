<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter\Configuration;

use Helmich\TypoScriptLint\Util\Filesystem;
use Symfony\Component\Config\Exception\FileLocatorFileNotFoundException;
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
 *
 * @psalm-suppress MethodSignatureMismatch
 */
class YamlConfigurationLoader extends FileLoader
{

    private YamlParser $yamlParser;

    private Filesystem $filesystem;

    /**
     * Constructs a new YAML-based configuration loader.
     *
     * @param FileLocatorInterface $locator The file locator.
     * @param YamlParser $yamlParser The YAML parser.
     * @param Filesystem $filesystem A filesystem interface.
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
     * @param mixed $resource The resource
     * @param string|null $type The resource type
     *
     * @return array<string, mixed>
     *
     * @psalm-suppress MethodSignatureMismatch
     */
    public function load(mixed $resource, ?string $type = null): array
    {
        assert(is_string($resource));
        try {
            /** @var string $path */
            $path = $this->locator->locate($resource);
            $file = $this->filesystem->openFile($path);

            /** @var array<string, mixed> $out */
            $out = $this->yamlParser->parse($file->getContents());
            return $out;
        } catch (FileLocatorFileNotFoundException $error) {
            return [];
        }
    }

    /**
     * Returns true if this class supports the given resource.
     *
     * @param mixed $resource A resource
     * @param string|null $type The resource type
     *
     * @return bool    true if this class supports the given resource, false otherwise
     *
     * @psalm-suppress MethodSignatureMismatch
     */
    public function supports(mixed $resource, ?string $type = null): bool
    {
        return is_string($resource)
            && in_array(pathinfo($resource, PATHINFO_EXTENSION), ['yml', 'yaml']);
    }
}
