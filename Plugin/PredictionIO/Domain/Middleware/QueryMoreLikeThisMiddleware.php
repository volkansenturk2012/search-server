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

use Apisearch\Model\ItemUUID;
use Apisearch\Model\User;
use Apisearch\Query\Filter;
use Apisearch\Server\Domain\Plugin\PluginMiddleware;
use Apisearch\Server\Domain\Query\Query;
use Apisearch\Query\Query as ModelQuery;

/**
 * Class QueryMoreLikeThisMiddleware
 */
class QueryMoreLikeThisMiddleware implements PluginMiddleware
{
    /**
     * @var array
     *
     * Query Server information
     */
    private $queryServer;

    /**
     * @var string
     *
     * Access key
     */
    private $accessKey;

    /**
     * InteractionMiddleware constructor.
     *
     * @param array  $queryServer
     * @param string $accessKey
     */
    public function __construct(
        array $queryServer,
        string $accessKey
    )
    {
        $this->queryServer = $queryServer;
        $this->accessKey = $accessKey;
    }

    /**
     * Execute middleware.
     *
     * @param Query $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute(
        $command,
        $next
    )
    {
        if ($command->getQuery()->getMetadata()['more_like_this']) {
            $this->addMoreLikeThis($command->getQuery());
        }

        return $next($command);
    }

    /**
     * Add more like this
     *
     * @param ModelQuery $query
     */
    private function addMoreLikeThis(ModelQuery $query)
    {
        $uuidFilter = $query->getFilter('_uuid');
        $user = $query->getUser();
        $uuids = $uuidFilter instanceof Filter
            ? $uuidFilter->getValues()
            : [];

        $content = [];
        if ($user instanceof User) {
            $content['user'] = $user->getId();
        }

        if (!empty($uuids)) {
            $content['itemSet'] = array_map(function(ItemUUID $itemUUID) {
                return $itemUUID->getId();
            }, $uuids);
        }

        $this->getContent($content);
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
            Query::class
        ];
    }

    /**
     * Post content to predictionIO
     *
     * Fire and forget
     *
     * @param array $content
     */
    function getContent(array $content)
    {
        $context = stream_context_create(
            array('http' =>
                array(
                    'method'  => 'GET',
                    'header'  => 'Content-type: application/json',
                    'content' => http_build_query($content)
                )
            )
        );

        $result = file_get_contents(
            "{$this->queryServer['host']}:{$this->queryServer['port']}",
            false, $context
        );
    }
}