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

namespace Apisearch\Server\Tests\Functional\Http;

use Apisearch\Plugin\Callbacks\CallbacksPluginBundle;
use Apisearch\Plugin\Elastica\ElasticaPluginBundle;
use Apisearch\Plugin\RedisStorage\RedisStoragePluginBundle;
use Apisearch\Plugin\RSQueue\RSQueuePluginBundle;
use Apisearch\Server\Tests\Functional\HttpFunctionalTest;

/**
 * Class HealthTest.
 */
class HealthTest extends HttpFunctionalTest
{
    /**
     * Test check health with different tokens.
     *
     * @param string $token
     * @param int    $responseCode
     *
     * @dataProvider dataCheckHealth
     */
    public function testCheckHealth(
        string $token,
        int $responseCode
    ) {
        $client = $this->createClient();
        $testRoute = static::get('router')->generate('search_server_api_check_health', [
            'token' => $token,
        ]);

        $client->request(
            'get',
            $testRoute
        );

        $response = $client->getResponse();
        $this->assertEquals(
            $responseCode,
            $response->getStatusCode()
        );

        if (200 === $responseCode) {
            $content = json_decode($response->getContent(), true);
            $this->assertTrue($content['healthy']);
            $this->assertTrue($content['status']['redis']);
            $this->assertTrue(
                in_array(
                    $content['status']['elasticsearch'],
                    ['green', 'yellow']
                )
            );
            $this->assertEquals(
                [
                    'callbacks' => CallbacksPluginBundle::class,
                    'elastica' => ElasticaPluginBundle::class,
                    'redis_storage' => RedisStoragePluginBundle::class,
                    'rsqueue' => RSQueuePluginBundle::class,
                ],
                $content['info']['plugins']
            );
        }
    }

    /**
     * Data for check health testing.
     *
     * @return array
     */
    public function dataCheckHealth(): array
    {
        self::loadEnv();

        return [
            [$_SERVER['APISEARCH_GOD_TOKEN'], 200],
            [$_SERVER['APISEARCH_PING_TOKEN'], 200],
            ['non-existing-key', 401],
        ];
    }
}
