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

use Apisearch\Server\Domain\Command\AddInteraction;

/**
 * Class AddInteractionHandler.
 */
class AddInteractionHandler
{
    /**
     * Add interaction.
     *
     * @param AddInteraction $addInteraction
     */
    public function handle(AddInteraction $addInteraction)
    {
        // Nothing to do here
    }
}
