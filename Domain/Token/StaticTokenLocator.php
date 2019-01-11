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

use Apisearch\Http\Endpoints;
use Apisearch\Model\AppUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;

/**
 * Class StaticTokenLocator.
 */
class StaticTokenLocator implements TokenLocator, TokenProvider
{
    /**
     * @var string
     *
     * God token
     */
    private $godToken;

    /**
     * @var string
     *
     * Readonly token
     */
    private $readonlyToken;

    /**
     * @var string
     *
     * Ping token
     */
    private $pingToken;

    /**
     * TokenValidator constructor.
     *
     * @param string $godToken
     * @param string $readonlyToken
     * @param string $pingToken
     */
    public function __construct(
        string $godToken,
        string $readonlyToken,
        string $pingToken
    ) {
        $this->godToken = $godToken;
        $this->readonlyToken = $readonlyToken;
        $this->pingToken = $pingToken;
    }

    /**
     * Locator is enabled.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return true;
    }

    /**
     * Get token by uuid.
     *
     * @param AppUUID   $appUUID
     * @param TokenUUID $tokenUUID
     *
     * @return Token|null
     */
    public function getTokenByUUID(
        AppUUID $appUUID,
        TokenUUID $tokenUUID
    ): ? Token {
        if ($tokenUUID->composeUUID() === $this->godToken) {
            return $this->createGodToken($appUUID);
        }

        if (
            !empty($this->readonlyToken) &&
            $tokenUUID->composeUUID() === $this->readonlyToken
        ) {
            return $this->createReadOnlyToken($appUUID);
        }

        if (
            !empty($this->pingToken) &&
            $tokenUUID->composeUUID() === $this->pingToken
        ) {
            return $this->createPingToken();
        }

        return null;
    }

    /**
     * Create god token instance.
     *
     * @param AppUUID $appUUID
     *
     * @return Token
     */
    private function createGodToken(AppUUID $appUUID): Token
    {
        return new Token(
            TokenUUID::createById($this->godToken),
            $appUUID,
            [],
            [],
            [],
            [],
            Token::INFINITE_DURATION,
            Token::INFINITE_HITS_PER_QUERY,
            Token::NO_CACHE
        );
    }

    /**
     * Create read only token instance.
     *
     * @param AppUUID $appUUID
     *
     * @return Token
     */
    private function createReadOnlyToken(AppUUID $appUUID): Token
    {
        return new Token(
            TokenUUID::createById($this->readonlyToken),
            $appUUID,
            [],
            [],
            Endpoints::compose(Endpoints::queryOnly()),
            [],
            Token::INFINITE_DURATION,
            Token::INFINITE_HITS_PER_QUERY,
            Token::DEFAULT_TTL
        );
    }

    /**
     * Create ping token instance.
     *
     * @return Token
     */
    private function createPingToken(): Token
    {
        return new Token(
            TokenUUID::createById($this->pingToken),
            AppUUID::createById(''),
            [],
            [],
            [
                'head~~/', // Ping
                'get~~/health', // Check health
            ],
            [],
            Token::INFINITE_DURATION,
            Token::INFINITE_HITS_PER_QUERY,
            Token::NO_CACHE
        );
    }

    /**
     * Get tokens by AppUUID.
     *
     * @param AppUUID $appUUID
     *
     * @return Token[]
     */
    public function getTokensByAppUUID(AppUUID $appUUID): array
    {
        $tokens = [
            $this->createGodToken($appUUID),
        ];

        if (!empty($this->readonlyToken)) {
            $tokens[] = $this->createReadOnlyToken($appUUID);
        }

        if (!empty($this->pingToken)) {
            $tokens[] = $this->createPingToken();
        }

        return $tokens;
    }
}
