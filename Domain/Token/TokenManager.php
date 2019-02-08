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

use Apisearch\Exception\InvalidTokenException;
use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;

/**
 * Class TokenManager.
 */
class TokenManager
{
    /**
     * @var TokenLocators
     *
     * Token locators
     */
    private $tokenLocators;

    /**
     * @var TokenValidators
     *
     * Token validators
     */
    private $tokenValidators;

    /**
     * TokenManager constructor.
     *
     * @param TokenLocators   $tokenLocators
     * @param TokenValidators $tokenValidators
     */
    public function __construct(
        TokenLocators $tokenLocators,
        TokenValidators $tokenValidators
    ) {
        $this->tokenLocators = $tokenLocators;
        $this->tokenValidators = $tokenValidators;
    }

    /**
     * Find and validate token given basic fields.
     *
     * If is valid, return valid Token
     *
     * @param AppUUID   $appUUID
     * @param IndexUUID $indexUUID
     * @param TokenUUID $tokenUUID
     * @param string    $referrer
     * @param string    $path
     * @param string    $verb
     *
     * @return Token $token
     */
    public function checkToken(
        AppUUID $appUUID,
        IndexUUID $indexUUID,
        TokenUUID $tokenUUID,
        string $referrer,
        string $path,
        string $verb
    ): Token {
        $token = $this->locateTokenByUUID($appUUID, $tokenUUID);

        if (
            !($token instanceof Token) ||
            !$this->isTokenValid(
                $token,
                $appUUID,
                $indexUUID,
                $referrer,
                $path,
                $verb
            )
        ) {
            throw InvalidTokenException::createInvalidTokenPermissions($tokenUUID->composeUUID());
        }

        return $token;
    }

    /**
     * Locate token by UUID.
     *
     * @param AppUUID   $appUUID
     * @param TokenUUID $tokenUUID
     *
     * @return Token|null
     */
    private function locateTokenByUUID(
        AppUUID $appUUID,
        TokenUUID $tokenUUID
    ): ? Token {
        $tokenLocators = $this
            ->tokenLocators
            ->getValidTokenLocators();

        foreach ($tokenLocators as $tokenLocator) {
            $token = $tokenLocator->getTokenByUUID(
                $appUUID,
                $tokenUUID
            );

            if ($token instanceof Token) {
                return $token;
            }
        }

        return null;
    }

    /**
     * Validate token given basic fields.
     *
     * If is valid, return valid Token
     *
     * @param AppUUID   $appUUID
     * @param IndexUUID $indexUUID
     * @param Token     $token
     * @param string    $referrer
     * @param string    $path
     * @param string    $verb
     *
     * @return bool
     */
    public function isTokenValid(
        Token $token,
        AppUUID $appUUID,
        IndexUUID $indexUUID,
        string $referrer,
        string $path,
        string $verb
    ): bool {
        $tokenValidators = $this
            ->tokenValidators
            ->getTokenValidators();

        foreach ($tokenValidators as $tokenValidator) {
            if (!$tokenValidator->isTokenValid(
                $token,
                $appUUID,
                $indexUUID,
                $referrer,
                $path,
                $verb
            )) {
                return false;
            }
        }

        return true;
    }
}
