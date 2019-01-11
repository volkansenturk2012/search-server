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

namespace Apisearch\Plugin\RedisStorage\Domain\Token;

use Apisearch\Model\AppUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Plugin\Redis\Domain\RedisWrapper;
use Apisearch\Repository\WithRepositoryReference;
use Apisearch\Repository\WithRepositoryReferenceTrait;
use Apisearch\Server\Domain\Repository\AppRepository\TokenRepository;
use Apisearch\Server\Domain\Token\TokenLocator;
use Apisearch\Server\Domain\Token\TokenProvider;

/**
 * Class TokenRedisRepository.
 */
class TokenRedisRepository implements TokenRepository, TokenLocator, TokenProvider, WithRepositoryReference
{
    use WithRepositoryReferenceTrait;

    /**
     * Redis hast id.
     *
     * @var string
     */
    const REDIS_KEY = 'apisearch_tokens';

    /**
     * @var RedisWrapper
     *
     * Redis wrapper
     */
    private $redisWrapper;

    /**
     * @var bool
     *
     * Enabled
     */
    private $enabled;

    /**
     * TokenRedisRepository constructor.
     *
     * @param RedisWrapper $redisWrapper
     * @param bool         $enabled
     */
    public function __construct(
        RedisWrapper $redisWrapper,
        bool $enabled
    ) {
        $this->redisWrapper = $redisWrapper;
        $this->enabled = $enabled;
    }

    /**
     * Locator is enabled.
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->enabled;
    }

    /**
     * Get composed redis key.
     *
     * @param AppUUID $appUUID
     *
     * @return string
     */
    private function composeRedisKey(AppUUID $appUUID): string
    {
        return $appUUID->composeUUID().'~~'.self::REDIS_KEY;
    }

    /**
     * Add token.
     *
     * @param Token $token
     */
    public function addToken(Token $token)
    {
        $this
            ->redisWrapper
            ->getClient()
            ->hSet(
                $this->composeRedisKey($this->getAppUUID()),
                $token->getTokenUUID()->composeUUID(),
                json_encode($token->toArray())
            );
    }

    /**
     * Delete token.
     *
     * @param TokenUUID $tokenUUID
     */
    public function deleteToken(TokenUUID $tokenUUID)
    {
        $this
            ->redisWrapper
            ->getClient()
            ->hDel(
                $this->composeRedisKey($this->getAppUUID()),
                $tokenUUID->composeUUID()
            );
    }

    /**
     * Get tokens.
     *
     * @return Token[]
     */
    public function getTokens(): array
    {
        return $this->getTokensByAppUUID($this->getAppUUID());
    }

    /**
     * Delete all tokens.
     */
    public function deleteTokens()
    {
        $this
            ->redisWrapper
            ->getClient()
            ->del($this->composeRedisKey($this->getAppUUID()));
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
        $token = $this
            ->redisWrapper
            ->getClient()
            ->hGet(
                $this->composeRedisKey($appUUID),
                $tokenUUID->composeUUID()
            );

        return false === $token
            ? null
            : Token::createFromArray(json_decode($token, true));
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
        $tokens = $this
            ->redisWrapper
            ->getClient()
            ->hGetAll($this->composeRedisKey($appUUID));

        return array_map(function (string $token) {
            return Token::createFromArray(json_decode($token, true));
        }, $tokens);
    }
}
