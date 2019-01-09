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

namespace Apisearch\Plugin\RabbitMQ\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseConfiguration;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;

/**
 * Class RabbitMQPluginConfiguration.
 */
class RabbitMQPluginConfiguration extends BaseConfiguration
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
                ->booleanNode('commands_queue_name')
                    ->defaultValue('apisearch:server:commands')
                ->end()
                ->booleanNode('events_queue_name')
                    ->defaultValue('apisearch:server:domain-events')
                ->end()
                ->booleanNode('busy_queue_name')
                    ->defaultValue('apisearch:server:busy')
                ->end()
                ->scalarNode('host')
                    ->defaultNull()
                ->end()
                ->integerNode('port')
                    ->defaultNull()
                ->end()
                ->scalarNode('user')
                    ->defaultFalse()
                ->end()
                ->scalarNode('password')
                    ->defaultNull()
                ->end();
    }
}
