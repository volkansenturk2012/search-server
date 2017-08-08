<?php

/*
 * This file is part of the Search Server Bundle.
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

namespace Puntmig\Search\Server\Domain\Event;

/**
 * Interface EventSubscriber.
 */
interface EventSubscriber
{
    /**
     * Subscriber should handle event.
     *
     * @param DomainEvent $event
     *
     * @return bool
     */
    public function shouldHandleEvent(DomainEvent $event) : bool;

    /**
     * Handle event.
     *
     * @param DomainEvent $event
     */
    public function handle(DomainEvent $event);
}