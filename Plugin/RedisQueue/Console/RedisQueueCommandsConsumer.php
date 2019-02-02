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

namespace Apisearch\Plugin\RedisQueue\Console;

use Apisearch\Plugin\RedisQueue\Domain\RedisQueueConsumerManager;
use Apisearch\Server\Domain\CommandConsumer\CommandConsumer;
use Apisearch\Server\Domain\Consumer\ConsumerManager;
use Apisearch\Server\Domain\ExclusiveCommand;
use ReflectionClass;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RedisQueueCommandsConsumer.
 */
class RedisQueueCommandsConsumer extends RedisQueueConsumer
{
    /**
     * @var CommandConsumer
     *
     * Command consumer
     */
    protected $commandConsumer;

    /**
     * RedisQueueConsumer constructor.
     *
     * @param RedisQueueConsumerManager $consumerManager
     * @param int                       $secondsToWaitOnBusy
     * @param CommandConsumer           $commandConsumer
     */
    public function __construct(
        RedisQueueConsumerManager $consumerManager,
        int $secondsToWaitOnBusy,
        CommandConsumer  $commandConsumer
    ) {
        parent::__construct(
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
     * @param array           $message
     * @param OutputInterface $output
     */
    protected function consumeMessage(
        array $message,
        OutputInterface $output
    ) {
        $commandNamespace = 'Apisearch\Server\Domain\Command\\'.$message['class'];
        $reflectionCommand = new ReflectionClass($commandNamespace);
        $consumerManager = $this->consumerManager;
        $isExclusiveCommand = $reflectionCommand->implementsInterface(ExclusiveCommand::class);

        if ($isExclusiveCommand) {
            $consumerManager->pauseConsumers([ConsumerManager::COMMAND_CONSUMER_TYPE]);
        }

        $this
            ->commandConsumer
            ->consumeCommand(
                $output,
                $message
            );

        if ($isExclusiveCommand) {
            $consumerManager->resumeConsumers([ConsumerManager::COMMAND_CONSUMER_TYPE]);
        }
    }
}
