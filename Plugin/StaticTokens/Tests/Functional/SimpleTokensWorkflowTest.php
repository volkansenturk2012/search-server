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

namespace Apisearch\Plugin\StaticTokens\Tests\Functional;

use Apisearch\Model\AppUUID;
use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Apisearch\Query\Query;

/**
 * Class SimpleTokensWorkflowTest.
 */
class SimpleStaticTokensWorkflowTest extends StaticTokensFunctionalTest
{
    /**
     * Test count.
     */
    public function testCount()
    {
        $this->assertCount(6, $this->getTokens());
    }

    /**
     * Test simple workflow.
     */
    public function testSimpleWorkflow()
    {
        $token = new Token(TokenUUID::createById('blablabla'), AppUUID::createById(self::$appId));
        $this->query(
            Query::createMatchAll(),
            null,
            null,
            $token
        );

        try {
            $this->query(
                Query::createMatchAll(),
                self::$anotherAppId,
                null,
                $token
            );

            $this->fail('Non accepted app should throw exception');
        } catch (\Exception $e) {
            // Pass
        }
    }

    /**
     * Test simple workflow.
     */
    public function testSpecificIndex()
    {
        $token = new Token(TokenUUID::createById('onlyindex'), AppUUID::createById(self::$appId));
        $this->query(
            Query::createMatchAll(),
            null,
            self::$index,
            $token
        );

        try {
            $this->query(
                Query::createMatchAll(),
                null,
                self::$anotherIndex,
                $token
            );

            $this->fail('Non accepted index should throw exception');
        } catch (\Exception $e) {
            // Pass
        }
    }

    /**
     * Test simple workflow.
     */
    public function testSpecificEndpoint()
    {
        $token = new Token(TokenUUID::createById('onlyaddtoken'), AppUUID::createById(self::$appId));
        $this->query(
            Query::createMatchAll(),
            null,
            null,
            $token
        )->getItems();

        try {
            $this->indexItems(
                [Item::create(new ItemUUID('1', 'lala'))],
                null,
                null,
                $token
            );

            $this->fail('Non accepted endpoints should throw exception');
        } catch (\Exception $e) {
            // Pass
        }
    }
}
