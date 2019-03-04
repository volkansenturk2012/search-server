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

use Apisearch\Exception\InvalidFormatException;
use Apisearch\Server\Tests\Functional\CurlFunctionalTest;

/**
 * Class MalformedQueryTest.
 */
class MalformedQueryTest extends CurlFunctionalTest
{
    /**
     * Test malformed query.
     *
     * @dataProvider dataMalformedQuery
     */
    public function testMalformedQuery(string $query)
    {
        try {
            self::makeCurl(
                'v1-query',
                self::$appId,
                self::$index,
                null,
                $query
            );
            $this->fail('InvalidFormatException should be thrown');
        } catch (InvalidFormatException $e) {
            // Silent pass
            $this->assertTrue(true);
        }
    }

    /**
     * Get malformed queries.
     *
     * @return array
     */
    public function dataMalformedQuery(): array
    {
        return [
            ['{"query":{}}}}'],
            ['{"query":{"aggregations":{"undefined":{"field":"indexed_metadata.anon"}}}}'],
            ['{"query":{"filters":{"bla":{"values":"string"}}}'],
            ['{"query":{"filters":{"bla":{"filter_type": "date_range", "values":"a1..a2"}}}}'],
        ];
    }
}
