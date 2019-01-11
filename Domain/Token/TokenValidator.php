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
use Carbon\Carbon;

/**
 * Class TokenValidator.
 */
class TokenValidator
{
    /**
     * @var TokenLocators
     *
     * Token locators
     */
    private $tokenLocators;

    /**
     * TokenValidator constructor.
     *
     * @param TokenLocators $tokenLocators
     */
    public function __construct(TokenLocators $tokenLocators)
    {
        $this->tokenLocators = $tokenLocators;
    }

    /**
     * Validate token given basic fields.
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
    public function validateToken(
        AppUUID $appUUID,
        IndexUUID $indexUUID,
        TokenUUID $tokenUUID,
        string $referrer,
        string $path,
        string $verb
    ): Token {
        $token = null;
        $tokenLocators = $this
            ->tokenLocators
            ->getValidTokenLocators();

        foreach ($tokenLocators as $tokenLocator) {
            $token = $tokenLocator->getTokenByUUID(
                $appUUID,
                $tokenUUID
            );

            if ($token instanceof Token) {
                break;
            }
        }

        $endpoint = strtolower($verb.'~~'.trim($path, '/'));

        if (
            (!$token instanceof Token) ||
            (
                $appUUID->composeUUID() !== $token->getAppUUID()->composeUUID()
            ) ||
            (
                !empty($token->getHttpReferrers()) &&
                !in_array($referrer, $token->getHttpReferrers())
            ) ||
            (
                !empty($indexUUID->composeUUID()) &&
                !empty($token->getIndices()) &&
                !array_reduce($token->getIndices(), function (bool $carry, IndexUUID $iterationIndexUUID) use ($indexUUID) {
                    return $carry && $iterationIndexUUID->composeUUID() === $indexUUID->composeUUID();
                }, true)
            ) ||
            (
                !empty($token->getEndpoints()) &&
                !in_array($endpoint, $token->getEndpoints())
            ) ||
            (
                $token->getSecondsValid() > 0 &&
                $token->getUpdatedAt() + $token->getSecondsValid() < Carbon::now('UTC')->timestamp
            )
        ) {
            throw InvalidTokenException::createInvalidTokenPermissions($tokenUUID->composeUUID());
        }

        return $token;
    }
}
