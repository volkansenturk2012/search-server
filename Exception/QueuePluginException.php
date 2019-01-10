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

namespace Apisearch\Server\Exception;

use LogicException;

/**
 * Class QueuePluginException.
 */
class QueuePluginException extends LogicException
{
    /**
     * Create Queue Plugin missing.
     *
     * return QueuePluginMissingException
     */
    public static function createQueuePluginMissingException(): QueuePluginException
    {
        return new self('Because you defined the command_bus to work with queues, you should enable one Queues plugin');
    }
}
