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
 * @author PuntMig Technologies
 */

declare(strict_types=1);

namespace Apisearch\Server\Controller;

use Apisearch\Event\HttpEventRepository;
use Apisearch\Exception\InvalidTokenException;
use Apisearch\Repository\HttpRepository;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Command\CreateEventsIndex;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class CreateEventsIndexController.
 */
class CreateEventsIndexController extends ControllerWithBus
{
    /**
     * Create an events index.
     *
     * @param Request $request
     *
     * @return JsonResponse
     *
     * @throws InvalidTokenException
     */
    public function createEventsIndex(Request $request)
    {
        $query = $request->query;

        $this
            ->commandBus
            ->handle(new CreateEventsIndex(
                RepositoryReference::create(
                    $query->get(HttpRepository::APP_ID_FIELD),
                    $query->get(HttpRepository::INDEX_FIELD)
                ),
                $request->request->get(HttpEventRepository::SHARDS_FIELD, 3),
                $request->request->get(HttpEventRepository::REPLICAS_FIELD, 2)
            ));

        return new JsonResponse('Events index created', 200);
    }
}
