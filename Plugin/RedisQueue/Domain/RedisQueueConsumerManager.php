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

namespace Apisearch\Plugin\RedisQueue\Domain;

use Apisearch\Plugin\Redis\Domain\RedisWrapper;
use Apisearch\Server\Domain\Consumer\ConsumerManager;

/**
 * Class RedisQueueConsumerManager.
 */
class RedisQueueConsumerManager extends ConsumerManager
{
    /**
     * @var RedisWrapper
     *
     * Redis wrapper
     */
    private $redisWrapper;

    /**
     * RedisQueueClient constructor.
     *
     * @param array        $queues
     * @param RedisWrapper $redisWrapper
     */
    public function __construct(
        array $queues,
        RedisWrapper $redisWrapper
    ) {
        parent::__construct($queues);
        $this->redisWrapper = $redisWrapper;
    }

    /**
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
        $this
            ->redisWrapper
            ->getClient()
            ->rPush(
                $this->queues['queues'][$type],
                json_encode($data)
            );
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

        return (int) $this
            ->redisWrapper
            ->getClient()
            ->lLen($queueName);
    }

    /**
     * Produce message.
     *
     * @param string $queue
     * @param array  $payload
     */
    public function reject(
        string $queue,
        array $payload
    ) {
        $this
            ->redisWrapper
            ->getClient()
            ->lPush(
                $queue,
                json_encode($payload)
            );
    }

    /**
     * Consume message.
     *
     * @param string $queueName
     *
     * @return array
     */
    public function consume(string $queueName): array
    {
        list($queueName, $payload) = $this
            ->redisWrapper
            ->getClient()
            ->blPop(
                [
                    $this->queues['busy_queues'][$queueName],
                    $this->queues['queues'][$queueName],
                ], 0
            );

        return [$queueName, json_decode($payload, true)];
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
                ->redisWrapper
                ->getClient()
                ->rPush(
                    $queue,
                    json_encode($value)
                );
        }
    }
}
