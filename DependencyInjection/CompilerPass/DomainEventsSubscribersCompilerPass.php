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

namespace Apisearch\Server\DependencyInjection\CompilerPass;

use Mmoreram\BaseBundle\CompilerPass\TagCompilerPass;

/**
 * Class DomainEventsSubscribersCompilerPass.
 */
class DomainEventsSubscribersCompilerPass extends TagCompilerPass
{
    /**
     * Get collector service name.
     *
     * @return string Collector service name
     */
    public function getCollectorServiceName(): string
    {
        return 'apisearch_server.event_publisher';
    }

    /**
     * Get collector method name.
     *
     * @return string Collector method name
     */
    public function getCollectorMethodName(): string
    {
        return 'subscribe';
    }

    /**
     * Get tag name.
     *
     * @return string Tag name
     */
    public function getTagName(): string
    {
        return 'apisearch_server.domain_event_subscriber';
    }
}
