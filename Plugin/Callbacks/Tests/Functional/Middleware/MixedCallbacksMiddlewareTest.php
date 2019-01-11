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
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Plugin\Callbacks\Tests\Functional\EndpointsFunctionalTest;
use Apisearch\Plugin\RedisStorage\RedisStoragePluginBundle;
use Apisearch\Query\Query;

/**
 * Class MixedCallbacksMiddlewareTest.
 */
class MixedCallbacksMiddlewareTest extends EndpointsFunctionalTest
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
                'my_query_callback_after' => [
                    'command' => 'Query',
                    'endpoint' => '/plugin/endpoints/change_query_result?'.static::getUrlQuery(),
                    'method' => 'GET',
                    'moment' => 'after',
                ],
                'my_query_callback_index_items' => [
                    'command' => 'IndexItems',
                    'endpoint' => '/plugin/endpoints/change_items?'.static::getUrlQuery(),
                    'method' => 'GET',
                    'moment' => 'before',
                ],
                'my_query_callback_add_token' => [
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
        $this->indexItems([
            Item::create(ItemUUID::createByComposedUUID('test~1')),
            Item::create(ItemUUID::createByComposedUUID('test~2')),
        ]);
        $this->addToken(new Token(TokenUUID::createById('lalaland'), AppUUID::createById(self::$appId)));
        $result = $this->query(Query::createMatchAll());
        $this->assertCount(7,
            $result->getItems()
        );

        foreach ($result->getItems() as $item) {
            $this->assertTrue(
                $item->get('modified')
            );
        }

        $this->assertCount(
            7,
            $this->query(Query::createMatchAll()
                ->filterBy('flag', 'flag', ['1'])
            )->getItems()
        );

        $this->assertInstanceOf(
            Token::class,
            $this->getTokens()['lalaland000']
        );
    }
}
