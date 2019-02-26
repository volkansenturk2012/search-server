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

namespace Apisearch\Plugin\StaticTokens\Domain\Token;

use Apisearch\Model\AppUUID;
use Apisearch\Model\IndexUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Server\Domain\Token\TokenLocator;
use Apisearch\Server\Domain\Token\TokenProvider;

/**
 * Class TokenRedisRepository.
 */
class StaticTokenLocator implements TokenLocator, TokenProvider
{
    /**
     * @var Token[]
     *
     * Tokens
     */
    private $tokens = [];

    /**
     * TokenRedisRepository constructor.
     *
     * @param array[] $tokensAsArray
     */
    public function __construct(array $tokensAsArray)
    {
        $tokens = [];
        array_walk($tokensAsArray, function (array $tokenAsArray, string $_) use (&$tokens) {
            $tokenAsArray['uuid'] = TokenUUID::createById($tokenAsArray['uuid'])->toArray();
            $tokenAsArray['app_uuid'] = AppUUID::createById($tokenAsArray['app_uuid'])->toArray();
            $tokenAsArray['indices'] = array_map(function (string $indexId) {
                return IndexUUID::createById($indexId)->toArray();
            }, $tokenAsArray['indices']);
            unset($tokenAsArray['app_id']);
            $tokenAsArray['created_at'] = null;
            $tokenAsArray['updated_at'] = null;
            $tokens[] = Token::createFromArray($tokenAsArray);
        });

        $this->tokens = $tokens;
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
        $tokens = array_values(
            array_filter(
                $this->tokens,
                function (Token $token) use ($appUUID, $tokenUUID) {
                    return
                        $token->getAppUUID()->composeUUID() === $appUUID->composeUUID() &&
                        $token->getTokenUUID()->composeUUID() === $tokenUUID->composeUUID();
                }
            )
        );

        return empty($tokens)
            ? null
            : $tokens[0];
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
        return array_values(
            array_filter(
                $this->tokens,
                function (Token $token) use ($appUUID) {
                    return $token->getAppUUID()->composeUUID() === $appUUID->composeUUID();
                }
            )
        );
    }
}
