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

use Apisearch\Server\Domain\CommandConsumer\CommandConsumer;
use Apisearch\Server\Domain\ExclusiveCommand;
use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use ReflectionClass;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RabbitMQCommandsConsumer.
 */
class RabbitMQCommandsConsumer extends Command
{
    /**
     * @var AMQPChannel
     *
     * Channel
     */
    private $channel;

    /**
     * @var CommandConsumer
     *
     * Command consumer
     */
    private $commandConsumer;

    /**
     * @var string
     *
     * Command queue name
     */
    private $commandQueueName;

    /**
     * @var string
     *
     * Busy queue name
     */
    private $busyQueueName;

    /**
     * @var bool
     *
     * Busy
     */
    private $busy = false;

    /**
     * ConsumerCommand constructor.
     *
     * @param AMQPChannel     $channel
     * @param CommandConsumer $commandConsumer
     * @param string          $commandQueueName
     * @param string          $busyQueueName
     */
    public function __construct(
        AMQPChannel        $channel,
        CommandConsumer $commandConsumer,
        string $commandQueueName,
        string $busyQueueName
    ) {
        parent::__construct();

        $this->channel = $channel;
        $this->commandConsumer = $commandConsumer;
        $this->commandQueueName = $commandQueueName;
        $this->busyQueueName = $busyQueueName;
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
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $channel = $this->channel;
        $channel->queue_declare($this->commandQueueName, false, false, false, false);
        $channel->exchange_declare($this->busyQueueName, 'fanout', false, false, false);
        $channel->basic_qos(null, 1, null);
        list($busy_queue_name) = $channel->queue_declare('', false, false, true, false);
        $channel->queue_bind($busy_queue_name, $this->busyQueueName);

        $channel->basic_consume($this->commandQueueName, '', false, false, false, false, function ($msg) use ($channel, $output) {
            $command = json_decode($msg->body, true);
            $commandNamespace = 'Apisearch\Server\Domain\Command\\'.$command['class'];
            $reflectionCommand = new ReflectionClass($commandNamespace);
            $isExclusiveCommand = $reflectionCommand->implementsInterface(ExclusiveCommand::class);

            while ($this->busy) {
                echo 'Busy... waiting 10 second...'.PHP_EOL;
                sleep(10);
                $channel->basic_reject($msg->delivery_info['delivery_tag'], true);

                return;
            }

            if ($isExclusiveCommand) {
                $channel->basic_publish(new AMQPMessage(true), $this->busyQueueName);
            }

            $this
                ->commandConsumer
                ->consumeCommand(
                    $output,
                    $command
                );

            $channel->basic_ack($msg->delivery_info['delivery_tag']);

            if ($isExclusiveCommand) {
                $channel->basic_publish(new AMQPMessage(false), $this->busyQueueName);
            }
        });

        $channel->basic_consume($busy_queue_name, '', false, true, false, false, function ($msg) use ($channel) {
            $this->busy = boolval($msg->body);
        });

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
