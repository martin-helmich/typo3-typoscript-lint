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



    public function getSniffConfigurations()
    {
        $sniffs = [];
        foreach ($this->configuration['sniffs'] as $class => $configuration)
        {
            $class = class_exists($class) ? $class : 'Helmich\\TypoScriptLint\\Linter\\Sniff\\' . $class . 'Sniff';

            $configuration['class'] = $class;
            $sniffs[]               = $configuration;
        }
        return $sniffs;
    }



    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     * @codeCoverageIgnore FU, I'm not going to test this one!
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();

        $root = $treeBuilder->root('tslint');
        $root
            ->children()
            ->arrayNode('sniffs')
            ->isRequired()
            ->useAttributeAsKey('class')
            ->prototype('array')
            ->children()
            ->scalarNode('class')->end()
            ->variableNode('parameters')->end()
            ->end()
            ->end()
            ->end()
            ->end();

        return $treeBuilder;
    }
}