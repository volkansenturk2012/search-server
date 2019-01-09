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

namespace Apisearch\Plugin\RabbitMQ\Domain;

use Apisearch\Server\Domain\EventEnqueuer\EventEnqueuer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQEventEnqueuer.
 */
class RabbitMQEventEnqueuer implements EventEnqueuer
{
    /**
     * @var AMQPChannel
     *
     * Channel
     */
    private $channel;

    /**
     * @var string
     *
     * Command queue name
     */
    private $domainEventQueueName;

    /**
     * RSQueueCommandEnqueuer constructor.
     *
     * @param AMQPChannel $channel
     * @param string      $domainEventQueueName
     */
    public function __construct(
        AMQPChannel $channel,
        string $domainEventQueueName
    ) {
        $this->channel = $channel;
        $this->domainEventQueueName = $domainEventQueueName;
    }

    /**
     * Enqueue a domain event.
     *
     * @param array $event
     */
    public function enqueueEvent(array $event)
    {
        $channel = $this->channel;
        $channel->queue_declare($this->domainEventQueueName, false, false, false, false);
        $channel->basic_publish(new AMQPMessage(json_encode($event)), '', $this->domainEventQueueName);
    }
}
