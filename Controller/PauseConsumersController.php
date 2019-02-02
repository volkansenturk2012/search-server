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

namespace Apisearch\Server\Controller;

use Apisearch\Exception\InvalidFormatException;
use Apisearch\Server\Domain\Command\PauseConsumers;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class PauseConsumersController.
 */
class PauseConsumersController extends ControllerWithBus
{
    /**
     * Ping.
     *
     * @return Response
     */
    public function __invoke(Request $request): Response
    {
        $this
            ->commandBus
            ->handle(new PauseConsumers(
                $this->getRequestContentObject(
                    $request,
                    'type',
                    InvalidFormatException::queryFormatNotValid($request->getContent()),
                    []
                )
            ));

        return new Response('Consumers are scheduled for being paused', Response::HTTP_ACCEPTED);
    }
}
