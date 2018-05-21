<?php declare(strict_types=1);

namespace Caciobanu\Symfony\GuzzleBundle\DependencyInjection;

use GuzzleHttp\Client;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder();

        $rootNode = $treeBuilder->root('caciobanu_guzzle');
        $rootNode
            ->children()
                ->arrayNode('clients')
                    ->useAttributeAsKey('name')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('client_class')->defaultValue(Client::class)->end()
                            ->scalarNode('base_uri')->isRequired()->end()
                            ->booleanNode('logging')
                                ->defaultFalse()
                                ->treatNullLike(false)
                            ->end()
                            ->arrayNode('options')
                                ->variablePrototype()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
