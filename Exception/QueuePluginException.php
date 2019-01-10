<?php
/**
 * File header placeholder
 */

namespace Apisearch\Server\Exception;

use LogicException;

/**
 * Class QueuePluginException
 */
class QueuePluginException extends LogicException
{
    /**
     * Create Queue Plugin missing
     *
     * return QueuePluginMissingException
     */
    public static function createQueuePluginMissingException() : QueuePluginException
    {
        return new self('Because you defined the command_bus to work with queues, you should enable one Queues plugin');
    }
}