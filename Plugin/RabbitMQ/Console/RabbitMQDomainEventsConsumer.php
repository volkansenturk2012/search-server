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

use Apisearch\Server\Domain\EventConsumer\EventConsumer;
use LogicException;
use PhpAmqpLib\Channel\AMQPChannel;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class RabbitMQDomainEventsConsumer.
 */
class RabbitMQDomainEventsConsumer extends Command
{
    /**
     * @var AMQPChannel
     *
     * Channel
     */
    private $channel;

    /**
     * @var EventConsumer
     *
     * Event consumer
     */
    private $eventConsumer;

    /**
     * @var string
     *
     * Event queue name
     */
    private $eventQueueName;

    /**
     * ConsumerCommand constructor.
     *
     * @param AMQPChannel   $channel
     * @param EventConsumer $eventConsumer
     * @param string        $eventQueueName
     */
    public function __construct(
        AMQPChannel        $channel,
        EventConsumer $eventConsumer,
        string $eventQueueName
    ) {
        parent::__construct();

        $this->channel = $channel;
        $this->eventConsumer = $eventConsumer;
        $this->eventQueueName = $eventQueueName;
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
        $channel->queue_declare($this->eventQueueName, false, false, false, false);

        $channel->basic_consume($this->eventQueueName, '', false, false, false, false, function ($msg) use ($channel, $output) {
            $this
                ->eventConsumer
                ->consumeDomainEvent(
                    $output,
                    json_decode($msg->body, true)
                );

            $channel->basic_ack($msg->delivery_info['delivery_tag']);
        });

        while (count($channel->callbacks)) {
            $channel->wait();
        }
    }
}
