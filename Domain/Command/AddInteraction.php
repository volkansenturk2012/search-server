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

use Apisearch\Model\Token;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\AppRequiredCommand;
use Apisearch\Server\Domain\AsynchronousableCommand;
use Apisearch\Server\Domain\CommandWithRepositoryReferenceAndToken;
use Apisearch\User\Interaction;

/**
 * Class AddInteraction.
 */
class AddInteraction extends CommandWithRepositoryReferenceAndToken implements AsynchronousableCommand, AppRequiredCommand
{
    /**
     * @var Interaction
     *
     * Interaction
     */
    private $interaction;

    /**
     * AddInteraction constructor.
     *
     *
     * @param RepositoryReference $repositoryReference
     * @param Token               $token
     * @param Interaction         $interaction
     */
    public function __construct(
        RepositoryReference $repositoryReference,
        Token              $token,
        Interaction $interaction
    ) {
        parent::__construct(
            $repositoryReference,
            $token
        );

        $this->interaction = $interaction;
    }

    /**
     * Get Interaction.
     *
     * @return Interaction
     */
    public function getInteraction(): Interaction
    {
        return $this->interaction;
    }

    /**
     * To array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'repository_reference' => $this
                ->getRepositoryReference()
                ->compose(),
            'token' => $this
                ->getToken()
                ->toArray(),
            'interaction' => $this
                ->interaction
                ->toArray(),
        ];
    }

    /**
     * Create command from array.
     *
     * @param array $data
     *
     * @return self
     */
    public static function fromArray(array $data)
    {
        return new self(
            RepositoryReference::createFromComposed($data['repository_reference']),
            Token::createFromArray($data['token']),
            Interaction::createFromArray($data['interaction'])
        );
    }
}
