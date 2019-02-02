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

namespace Apisearch\Plugin\RedisQueue\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class RedisQueuePluginConfiguration.
 */
class RedisQueuePluginConfiguration extends BaseConfiguration
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
                ->scalarNode('commands_queue_name')
                    ->defaultValue('apisearch_commands')
                ->end()
                ->scalarNode('commands_busy_queue_name')
                    ->defaultValue('apisearch_commands_busy')
                ->end()
                ->scalarNode('events_queue_name')
                    ->defaultValue('apisearch_domain_events')
                ->end()
                ->scalarNode('events_busy_queue_name')
                    ->defaultValue('apisearch_domain_events_busy')
                ->end()
                ->scalarNode('host')
                    ->defaultNull()
                ->end()
                ->integerNode('port')
                    ->defaultNull()
                ->end()
                ->booleanNode('is_cluster')
                    ->defaultFalse()
                ->end()
                ->scalarNode('database')
                    ->defaultNull()
                ->end()
                ->integerNode('seconds_to_wait_on_busy')
                    ->defaultValue(10)
                ->end();
    }
}
