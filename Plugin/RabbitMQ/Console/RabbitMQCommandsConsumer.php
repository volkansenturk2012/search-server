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

namespace Apisearch\Plugin\RabbitMQ\Console;

use Apisearch\Plugin\RabbitMQ\Domain\RabbitMQChannel;
use Apisearch\Server\Domain\CommandConsumer\CommandConsumer;
use Apisearch\Server\Domain\Consumer\ConsumerManager;
use Apisearch\Server\Domain\ExclusiveCommand;
use PhpAmqpLib\Message\AMQPMessage;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RabbitMQCommandsConsumer.
 */
class RabbitMQCommandsConsumer extends RabbitMQConsumer
{
    /**
     * @var CommandConsumer
     *
     * Command consumer
     */
    protected $commandConsumer;

    /**
     * ConsumerCommand constructor.
     *
     * @param RabbitMQChannel $channel
     * @param ConsumerManager $consumerManager
     * @param int             $secondsToWaitOnBusy
     * @param CommandConsumer $commandConsumer
     */
    public function __construct(
        RabbitMQChannel        $channel,
        ConsumerManager $consumerManager,
        int $secondsToWaitOnBusy,
        CommandConsumer $commandConsumer
    ) {
        parent::__construct(
            $channel,
            $consumerManager,
            $secondsToWaitOnBusy
        );

        $this->commandConsumer = $commandConsumer;
    }

    /**
     * Get queue type.
     *
     * @return string
     */
    protected function getQueueType(): string
    {
        return ConsumerManager::COMMAND_CONSUMER_TYPE;
    }

    /**
     * Consume message.
     *
     * @param AMQPMessage     $message
     * @param OutputInterface $output
     */
    protected function consumeMessage(
        AMQPMessage $message,
        OutputInterface $output
    ) {
        $consumerManager = $this->consumerManager;
        $command = json_decode($message->body, true);
        $commandNamespace = 'Apisearch\Server\Domain\Command\\'.$command['class'];
        $reflectionCommand = new ReflectionClass($commandNamespace);
        $isExclusiveCommand = $reflectionCommand->implementsInterface(ExclusiveCommand::class);

        if ($isExclusiveCommand) {
            $consumerManager->pauseConsumers([ConsumerManager::COMMAND_CONSUMER_TYPE]);
        }

        $this
            ->commandConsumer
            ->consumeCommand(
                $output,
                $command
            );

        $this
            ->channel
            ->getChannel()
            ->basic_ack($message->delivery_info['delivery_tag']);

        if ($isExclusiveCommand) {
            $consumerManager->resumeConsumers([ConsumerManager::COMMAND_CONSUMER_TYPE]);
        }
    }
}
