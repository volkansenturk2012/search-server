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

namespace Apisearch\Server\Tests\Functional\Domain\Repository;

use Apisearch\Query\Query;
use Apisearch\Server\Tests\Functional\AsynchronousFunctionalTest;

/**
 * Class AsynchronousCommandsTest.
 */
abstract class AsynchronousCommandsTest extends AsynchronousFunctionalTest
{
    use AllAsynchronousTests;

    /**
     * Test simple query.
     *
     * We start sleeping 2 seconds to make sure that the commands are properly
     * ingested and processed by the command consumer
     */
    public function testSimpleQuery()
    {
        sleep(1);
        $this->assertCount(
            5,
            $this
                ->query(Query::createMatchAll())
                ->getItems()
        );
    }
}
