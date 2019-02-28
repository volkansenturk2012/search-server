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

declare(strict_types = 1);

namespace Apisearch\Plugin\PredictionIO\Domain\Middleware;

use Apisearch\Server\Domain\Command\AddInteraction;
use Apisearch\Server\Domain\Plugin\PluginMiddleware;
use Apisearch\User\Interaction;
use DateTime;

/**
 * Class InteractionMiddleware
 */
class InteractionMiddleware implements PluginMiddleware
{
    /**
     * @var array
     *
     * Event Server information
     */
    private $eventServer;

    /**
     * @var string
     *
     * Access key
     */
    private $accessKey;

    /**
     * InteractionMiddleware constructor.
     *
     * @param array  $eventServer
     * @param string $accessKey
     */
    public function __construct(
        array $eventServer,
        string $accessKey
    )
    {
        $this->eventServer = $eventServer;
        $this->accessKey = $accessKey;
    }

    /**
     * Execute middleware.
     *
     * @param AddInteraction $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute(
        $command,
        $next
    )
    {
        $this->putInteraction($command->getInteraction());

        return $next($command);
    }

    /**
     * Put interaction to predictionIO
     *
     * @param Interaction $interaction
     */
    private function putInteraction(Interaction $interaction)
    {
        $this->postContent([
            'event' => $interaction->getEventName(),
            'entityType' => 'user',
            'entityId' => $interaction->getUser()->getId(),
            'targetEntityType' => 'item',
            'targetEntityId' => $interaction->getItemUUID()->composeUUID(),
            'eventTime' => (new DateTime)->format(DateTime::ATOM)
        ]);
    }

    /**
     * Commands subscribed namespace. Can refer to specific class namespace, any
     * parent class or any interface.
     *
     * By returning an empty array, means coupled to all.
     *
     * @return string[]
     */
    public function getSubscribedCommands(): array
    {
        return [
            AddInteraction::class
        ];
    }

    /**
     * Post content to predictionIO
     *
     * Fire and forget
     *
     * @param array $content
     */
    function postContent(array $content)
    {
        $fp = fsockopen($this->eventServer['host'], $this->eventServer['port'], $errno, $errstr, 30);
        $data = json_encode($content);
        $accessKey = $this->accessKey;
        $out = "POST /events.json?accessKey=$accessKey HTTP/1.1\r\n";
        $out.= "Host: {$this->eventServer['host']}:{$this->eventServer['port']}\r\n";
        $out.= "Content-Type: application/x-www-form-urlencoded\r\n";
        $out.= "Content-Length: ".strlen($data)."\r\n";
        $out.= "Connection: Close\r\n\r\n";
        $out.= $data;

        fwrite($fp, $out);
        fclose($fp);
    }
}