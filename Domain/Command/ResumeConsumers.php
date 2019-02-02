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

namespace Apisearch\Server\Domain\Command;

/**
 * Class ResumeConsumers.
 */
class ResumeConsumers
{
    /**
     * @var string[]
     *
     * Types
     */
    private $types;

    /**
     * PauseConsumers constructor.
     *
     * @param array $types
     */
    public function __construct(array $types)
    {
        $this->types = $types;
    }

    /**
     * Get types.
     *
     * @return string[]
     */
    public function getTypes(): array
    {
        return $this->types;
    }
}
