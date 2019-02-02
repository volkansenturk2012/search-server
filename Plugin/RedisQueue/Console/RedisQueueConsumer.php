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

use Apisearch\Command\ApisearchCommand;
use Apisearch\Plugin\RedisQueue\Domain\RedisQueueConsumerManager;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RedisQueueConsumer.
 */
abstract class RedisQueueConsumer extends ApisearchCommand
{
    /**
     * @var RedisQueueConsumerManager
     *
     * Consumer Manager
     */
    protected $consumerManager;

    /**
     * @var int
     *
     * Seconds to wait on busy
     */
    protected $secondsToWaitOnBusy;

    /**
     * @var bool
     *
     * Busy
     */
    protected $busy = false;

    /**
     * RedisQueueConsumer constructor.
     *
     * @param RedisQueueConsumerManager $consumerManager
     * @param int                       $secondsToWaitOnBusy
     */
    public function __construct(
        RedisQueueConsumerManager $consumerManager,
        int $secondsToWaitOnBusy
    ) {
        parent::__construct();

        $this->consumerManager = $consumerManager;
        $this->secondsToWaitOnBusy = $secondsToWaitOnBusy;
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return int|null null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startCommand($output);
        $consumerManager = $this->consumerManager;
        $consumerBusyQueueName = $consumerManager->getQueueName($this->getQueueType(), true);

        while (true) {
            list($givenQueue, $payload) = $consumerManager->consume($this->getQueueType());

            /*
             * Busy queue
             */
            if ($givenQueue === $consumerBusyQueueName) {
                $this->busy = boolval($payload);

                $this->printInfoMessage($output, 'Redis', ($this->busy ? 'Paused' : 'Resumed').' consumer');

            /*
             * Regular queue + busy
             */
            } elseif ($this->busy) {
                $output->writeln('Busy channel. Rejecting and waiting '.$this->secondsToWaitOnBusy.' seconds');
                $consumerManager->reject($givenQueue, $payload);
                sleep($this->secondsToWaitOnBusy);

            /*
             * Regular queue
             */
            } else {
                $this->consumeMessage(
                    $payload,
                    $output
                );
            }
        }

        return 0;
    }

    /**
     * Get queue type.
     *
     * @return string
     */
    abstract protected function getQueueType(): string;

    /**
     * Consume message.
     *
     * @param array           $message
     * @param OutputInterface $output
     */
    abstract protected function consumeMessage(
        array $message,
        OutputInterface $output
    );
}
