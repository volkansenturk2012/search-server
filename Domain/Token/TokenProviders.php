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

namespace Apisearch\Server\Domain\Token;

use Apisearch\Model\AppUUID;
use Apisearch\Model\Token;

/**
 * Class TokenProviders.
 */
class TokenProviders
{
    /**
     * @var TokenProvider[]
     *
     * Token provider
     */
    private $tokenProviders = [];

    /**
     * Add token provider.
     *
     * @param TokenProvider $tokenProvider
     */
    public function addTokenProvider(TokenProvider $tokenProvider)
    {
        $this->tokenProviders[] = $tokenProvider;
    }

    /**
     * Get tokens.
     *
     * @param AppUUID $appUUID
     *
     * @return Token[]
     */
    public function getTokensByAppUUID(AppUUID $appUUID): array
    {
        $tokens = [];
        foreach ($this->tokenProviders as $tokenProvider) {
            $tokens = array_merge($tokens, $tokenProvider->getTokensByAppUUID($appUUID));
        }

        return $tokens;
    }
}
