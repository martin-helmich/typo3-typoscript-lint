<?php declare(strict_types=1);
namespace Helmich\TypoScriptLint\Linter;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class LinterConfiguration implements ConfigurationInterface
{

    private $configuration;

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
        return $this->configuration['filePatterns'] ?: [];
    }

    /**
     * @return array
     */
    public function getSniffConfigurations(): array
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
     * @suppress PhanParamTooMany, PhanUndeclaredMethod
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        if (method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('typoscript-lint');
            $root = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $root = $treeBuilder->root('typoscript-lint');
        }

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
