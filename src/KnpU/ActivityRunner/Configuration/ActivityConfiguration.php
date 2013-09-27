<?php

namespace KnpU\ActivityRunner\Configuration;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;

/**
 * @author Kristen Gilden <kristen.gilden@knplabs.com>
 */
class ActivityConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('activities');

        $rootNode
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('question')->end()
                    ->arrayNode('skeletons')
                        ->cannotBeEmpty()
                        ->defaultValue(array(
                            'skeleton.html.twig' => __DIR__.'skeleton.html.twig'
                        ))
                        ->prototype('scalar')->end()
                    ->end()
                    ->scalarNode('worker')
                        ->isRequired()
                        ->cannotBeEmpty()
                        ->validate()
                        ->ifNotInArray(array('php', 'twig'))
                            ->thenInvalid('Invalid worker "%s"')
                        ->end()
                    ->end()
                    ->scalarNode('entry_point')
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('context')->defaultValue(false)->end()
                    ->scalarNode('asserts')
                        ->cannotBeEmpty()
                        ->defaultValue('asserts.php')
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
