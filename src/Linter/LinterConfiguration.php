<?php declare(strict_types=1);

namespace Helmich\TypoScriptLint\Linter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @phpstan-type LinterSniffConfigurationArray array{
 *     disabled?: bool,
 *     parameters?: mixed,
 * }
 * @phpstan-type LinterConfigurationArray array{
 *     paths?: string[],
 *     filePatterns?: string[],
 *     excludePatterns?: string[],
 *     sniffs: array<string, LinterSniffConfigurationArray>
 * }
 * @phpstan-type SniffConfigurationArray array{
 *     class: class-string,
 *     parameters?: mixed,
 * }
 */
class LinterConfiguration implements ConfigurationInterface
{

    /**
     * @var LinterConfigurationArray
     */
    private array $configuration = ['sniffs' => []];

    /**
     * @param LinterConfigurationArray $configuration
     * @return void
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }

    /**
     * Get the list of paths to lint
     *
     * @return string[]
     */
    public function getPaths(): array
    {
        $paths = [];

        if (!empty($this->configuration['paths'])) {
            $paths = $this->configuration['paths'];
        }

        return $paths;
    }

    /**
     * Returns the list of supported filename patterns.
     *
     * @return string[]
     */
    public function getFilePatterns(): array
    {
        return $this->configuration['filePatterns'] ?? [];
    }

    /**
     * Returns the list of filename patterns that should be excluded even if supported.
     *
     * @return string[]
     */
    public function getExcludePatterns(): array
    {
        return $this->configuration['excludePatterns'] ?? [];
    }

    /**
     * @return SniffConfigurationArray[]
     */
    public function getSniffConfigurations(): array
    {
        $sniffs = [];
        foreach ($this->configuration['sniffs'] as $class => $configuration) {
            if (isset($configuration['disabled']) && $configuration['disabled']) {
                continue;
            }

            /** @var class-string $class */
            $class = class_exists($class) ? $class : 'Helmich\\TypoScriptLint\\Linter\\Sniff\\' . $class . 'Sniff';

            $configuration['class'] = $class;
            $sniffs[] = $configuration;
        }
        return $sniffs;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     * @codeCoverageIgnore FU, I'm not going to test this one!
     *
     * @noinspection       PhpUndefinedMethodInspection
     * @noinspection       PhpParamsInspection
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        // @phpstan-ignore-next-line
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('typoscript-lint');
            $root = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder(); // @phpstan-ignore-line
            $root = $treeBuilder->root('typoscript-lint'); // @phpstan-ignore-line
        }

        $root
            ->children()
            ->arrayNode('paths')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('filePatterns')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('excludePatterns')
            ->prototype('scalar')->end()
            ->end()
            ->arrayNode('sniffs')
            ->isRequired()
            ->useAttributeAsKey('class')
            ->prototype('array')
            ->children()
            ->scalarNode('class')->end()
            ->variableNode('parameters')->end()
            ->booleanNode('disabled')->defaultValue(false)->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}
