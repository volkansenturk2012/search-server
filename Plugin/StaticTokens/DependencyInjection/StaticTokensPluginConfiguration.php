<?php

/*
 * This file is part of the Apisearch Server
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

declare(strict_types=1);

namespace Apisearch\Plugin\StaticTokens\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class StaticTokensPluginConfiguration.
 */
class StaticTokensPluginConfiguration extends BaseConfiguration
{
    /**
     * Configure the root node.
     *
     * @param ArrayNodeDefinition $rootNode Root node
     */
    protected function setupTree(ArrayNodeDefinition $rootNode)
    {
        $rootNode
            ->children()
                ->arrayNode('tokens')
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('app_id')
                                ->isRequired()
                            ->end()
                            ->arrayNode('indices')
                                ->scalarPrototype()->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('endpoints')
                                ->scalarPrototype()
                                ->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('plugins')
                                ->scalarPrototype()
                                ->end()
                                ->defaultValue([])
                            ->end()
                            ->arrayNode('metadata')
                                ->children()
                                    ->arrayNode('http_referrers')
                                        ->scalarPrototype()->end()
                                        ->defaultValue([])
                                    ->end()
                                    ->integerNode('seconds_valid')
                                        ->defaultValue(0)
                                    ->end()
                                    ->integerNode('max_hits_per_query')
                                        ->defaultValue(0)
                                    ->end()
                                    ->integerNode('ttl')
                                        ->defaultValue(60)
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end();
    }
}
