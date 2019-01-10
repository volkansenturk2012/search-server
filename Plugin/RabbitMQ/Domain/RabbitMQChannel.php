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

use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;

/**
 * Class RabbitMQChannel.
 */
class RabbitMQChannel
{
    /**
     * Get channel.
     *
     * @param string $host
     * @param int    $port
     * @param string $user
     * @param string $password
     * @param string $vhost
     *
     * @return AMQPChannel
     */
    public static function create(
        string $host,
        int $port,
        string $user,
        string $password,
        string $vhost
    ): AMQPChannel {
        $connection = new AMQPStreamConnection(
            $host,
            $port,
            $user,
            $password,
            $vhost
        );

        return $connection->channel();
    }
}
