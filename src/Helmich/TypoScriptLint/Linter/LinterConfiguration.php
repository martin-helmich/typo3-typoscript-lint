<?php
namespace Helmich\TypoScriptLint\Linter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class LinterConfiguration implements ConfigurationInterface
{

    private $configuration;

    public function setConfiguration(array $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * Get the list of paths to lint
     *
     * @return string[]
     */
    public function getPaths()
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
    public function getFilePatterns()
    {
        return $this->configuration['filePatterns'] ?: [];
    }

    public function getSniffConfigurations()
    {
        $sniffs = [];
        foreach ($this->configuration['sniffs'] as $class => $configuration) {
            if (isset($configuration['disabled']) && $configuration['disabled']) {
                continue;
            }

            $class = class_exists($class) ? $class : 'Helmich\\TypoScriptLint\\Linter\\Sniff\\' . $class . 'Sniff';

            $configuration['class'] = $class;
            $sniffs[]               = $configuration;
        }
        return $sniffs;
    }

    /**
     * Generates the configuration tree builder.
     *
     * @return TreeBuilder The tree builder
     * @codeCoverageIgnore FU, I'm not going to test this one!
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $root = $treeBuilder->root('tslint');
        $root
            ->children()
                ->arrayNode('paths')
                    ->prototype('scalar')->end()
                ->end()
                ->arrayNode('filePatterns')
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
