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

use Apisearch\Server\Domain\CommandEnqueuer\CommandEnqueuer;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQCommandEnqueuer.
 */
class RabbitMQCommandEnqueuer implements CommandEnqueuer
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
    private $commandQueueName;

    /**
     * RSQueueCommandEnqueuer constructor.
     *
     * @param AMQPChannel $channel
     * @param string      $commandQueueName
     */
    public function __construct(
        AMQPChannel $channel,
        string $commandQueueName
    ) {
        $this->channel = $channel;
        $this->commandQueueName = $commandQueueName;
    }

    /**
     * Enqueue a command.
     *
     * @param object $command
     */
    public function enqueueCommand($command)
    {
        $commandAsArray = $command->toArray();
        $commandAsArray['class'] = str_replace('Apisearch\Server\Domain\Command\\', '', get_class($command));
        $channel = $this->channel;

        $channel->queue_declare($this->commandQueueName, false, false, false, false);
        $channel->basic_publish(new AMQPMessage(json_encode($commandAsArray)), '', $this->commandQueueName);
    }
}
