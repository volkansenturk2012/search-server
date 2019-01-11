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

namespace Apisearch\Plugin\Callbacks\Tests\Functional\Middleware\Query;

use Apisearch\Model\AppUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Plugin\Callbacks\Tests\Functional\EndpointsFunctionalTest;
use Apisearch\Plugin\RedisStorage\RedisStoragePluginBundle;

/**
 * Class CallbacksMiddlewareAddTokenChangeBeforeTest.
 */
class CallbacksMiddlewareAddTokenChangeBeforeTest extends EndpointsFunctionalTest
{
    /**
     * Decorate bundles.
     *
     * @param array $bundles
     *
     * @return array
     */
    protected static function decorateBundles(array $bundles): array
    {
        $bundles = parent::decorateBundles($bundles);
        $bundles[] = RedisStoragePluginBundle::class;

        return $bundles;
    }

    /**
     * Get callbacks configuration.
     *
     * @return array
     */
    protected static function getCallbacksConfiguration(): array
    {
        return [
            'http_client_adapter' => 'http_test',
            'callbacks' => [
                'my_query_callback_before' => [
                    'command' => 'AddToken',
                    'endpoint' => '/plugin/endpoints/change_token?'.static::getUrlQuery(),
                    'method' => 'GET',
                    'moment' => 'before',
                ],
            ],
        ];
    }

    /**
     * Test something.
     */
    public function testSomething()
    {
        $this->addToken(new Token(TokenUUID::createById('lalaland'), AppUUID::createById(self::$appId)));
        $tokens = $this->getTokens();
        $this->assertInstanceOf(
            Token::class,
            $tokens['lalaland000']
        );
    }
}
