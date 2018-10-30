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

use Apisearch\Server\Tests\Functional\HttpFunctionalTest;

/**
 * Class PingTest.
 */
class PingTest extends HttpFunctionalTest
{
    /**
     * Test ping with different tokens.
     *
     * @param string $token
     * @param int    $responseCode
     *
     * @dataProvider dataPing
     */
    public function testPing(
        string $token,
        int $responseCode
    ) {
        $client = $this->createClient();
        $testRoute = static::get('router')->generate('search_server_api_ping', [
            'token' => $token,
        ]);

        $client->request(
            'head',
            $testRoute
        );

        $this->assertEquals(
            $responseCode,
            $client->getResponse()->getStatusCode()
        );
    }

    /**
     * Data for ping testing.
     *
     * @return array
     */
    public function dataPing(): array
    {
        self::loadEnv();

        return [
            [$_SERVER['APISEARCH_GOD_TOKEN'], 200],
            [$_SERVER['APISEARCH_PING_TOKEN'], 200],
            ['1234', 401],
        ];
    }
}
