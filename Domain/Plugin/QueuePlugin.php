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

namespace Apisearch\Server\Domain\Plugin;

/**
 * Interface QueuePlugin.
 *
 * A plugin of Queue type must create these services, each one implementing
 * these interfaces
 *
 *  -
 *      type: Service
 *      service name: apisearch_server.command_enqueuer
 *      description: Enqueue Commands
 *      interface: Apisearch\Server\Domain\CommandEnqueuer\CommandEnqueuer
 *
 *  -
 *      type: Service
 *      service name: apisearch_server.event_enqueuer
 *      description: Enqueue Domain events
 *      interface: Apisearch\Server\Domain\EventEnqueuer\EventEnqueuer
 *
 *  -
 *      type: Command
 *      command name: apisearch-consumer:domain-events
 *      description: Consumer for domain events
 *
 *  -
 *      type: Command
 *      command name: apisearch-consumer:commands
 *      description: Consumer for commands
 */
interface QueuePlugin
{
}
