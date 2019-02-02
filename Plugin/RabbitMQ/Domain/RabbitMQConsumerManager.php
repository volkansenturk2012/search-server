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

use Apisearch\Server\Domain\Consumer\ConsumerManager;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;

/**
 * Class RabbitMQConsumerManager.
 */
class RabbitMQConsumerManager extends ConsumerManager
{
    /**
     * @var AMQPChannel
     *
     * Channel
     */
    private $channel;

    /**
     * RabbitMQConsumerManager constructor.
     *
     * @param array       $queues
     * @param AMQPChannel $channel
     */
    public function __construct(
        array $queues,
        AMQPChannel $channel
    ) {
        parent::__construct($queues);
        $this->channel = $channel;
    }

    /**
     * Declare consumer and return if was ok.
     *
     * @param string $type
     *
     * @return bool
     */
    public function declareConsumer(string $type): bool
    {
        $queueName = $this->queues['queues'][$type] ?? null;

        if (is_null($queueName)) {
            return false;
        }

        $this
            ->channel
            ->queue_declare($queueName, false, false, false, false);

        return true;
    }

    /**
     * Declare busy channel and return the queue name if was ok.
     *
     * @param string $type
     *
     * @return string|null
     */
    public function declareBusyChannel(string $type): ? string
    {
        $busyQueueName = $this->queues['busy_queues'][$type] ?? null;
        if (is_null($busyQueueName)) {
            return null;
        }

        $channel = $this->channel;
        $channel->exchange_declare($busyQueueName, 'fanout', false, false, false);
        list($createdBusyQueueName) = $channel->queue_declare('', false, false, true, false);
        $channel->queue_bind($createdBusyQueueName, $busyQueueName);

        return $createdBusyQueueName;
    }

    /**
     * Declare busy channel.
     *
     * @param string $type
     * @param mixed  $data
     */
    public function enqueue(
        string $type,
        $data
    ) {
        if (is_null($this->declareConsumer($type))) {
            return;
        }

        $this
            ->channel
            ->basic_publish(new AMQPMessage(json_encode($data), [
                'delivery_mode' => 2,
            ]), '', $this->queues['queues'][$type]);
    }

    /**
     * Get queue size.
     *
     * @param string $type
     *
     * @return int|null
     */
    public function getQueueSize(string $type): ? int
    {
        $queueName = $this->queues['queues'][$type] ?? null;

        if (is_null($queueName)) {
            return false;
        }

        $data = $this
            ->channel
            ->queue_declare($queueName, true);

        return \intval($data[1]);
    }

    /**
     * Send to queues a boolean value, given queues.
     *
     * @param string[] $queues
     * @param bool     $value
     */
    protected function sendBooleanToQueues(
        array $queues,
        bool $value
    ) {
        foreach ($queues as $queue) {
            $this
                ->channel
                ->basic_publish(new AMQPMessage($value), $queue);
        }
    }
}
