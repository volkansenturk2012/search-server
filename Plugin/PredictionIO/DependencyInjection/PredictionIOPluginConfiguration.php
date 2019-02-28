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

namespace Apisearch\Plugin\PredictionIO\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class PredictionIOPluginConfiguration.
 */
class PredictionIOPluginConfiguration extends BaseConfiguration
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
                ->arrayNode('event_server')
                    ->children()
                        ->scalarNode('endpoint')
                            ->isRequired()
                        ->end()
                        ->scalarNode('port')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('query_server')
                    ->children()
                        ->scalarNode('endpoint')
                            ->isRequired()
                        ->end()
                        ->scalarNode('port')
                            ->isRequired()
                        ->end()
                    ->end()
                ->end()
                ->scalarNode('access_key')
                    ->defaultValue('')
                ->end();
    }
}
