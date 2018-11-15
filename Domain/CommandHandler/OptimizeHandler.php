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

namespace Apisearch\Server\Domain\CommandHandler;

use Apisearch\Server\Domain\Command\Optimize;
use Apisearch\Server\Domain\Event\DomainEventWithRepositoryReference;
use Apisearch\Server\Domain\Event\IndexWasReset;
use Apisearch\Server\Domain\WithAppRepositoryAndEventPublisher;

/**
 * Class OptimizeHandler.
 */
class OptimizeHandler extends WithAppRepositoryAndEventPublisher
{
    /**
     * Optimize the index.
     *
     * @param Optimize $optimize
     */
    public function handle(Optimize $optimize)
    {
        $repositoryReference = $optimize->getRepositoryReference();

        $this
            ->appRepository
            ->setRepositoryReference($repositoryReference);

        $this
            ->appRepository
            ->resetIndex($indexUUID);

        $this
            ->eventPublisher
            ->publish(new DomainEventWithRepositoryReference(
                $repositoryReference,
                new IndexWasReset($indexUUID)
            ));
    }
}
