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

use Apisearch\Query\Filter;
use Apisearch\Query\Query;
use Apisearch\Query\ScoreStrategies;
use Apisearch\Query\ScoreStrategy;
use Apisearch\Query\SortBy;

/**
 * Class ScoreStrategyTest.
 */
trait ScoreStrategyTest
{
    /**
     * Test default strategy.
     */
    public function testDefaultStrategy()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createDefault())
                )
        );

        $this->assertResults(
            $result,
            ['1', '2', '3', '4', '5']
        );
    }

    /**
     * Test relevance strategy.
     */
    public function testRelevanceStrategyFieldValue()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createFieldBoosting(
                            'relevance'
                        ))
                )
        );

        $this->assertResults(
            $result,
            ['5', '{1', '4}', '3', '2']
        );
    }

    /**
     * Test custom function strategy.
     */
    public function testCustomFunctionStrategy()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createCustomFunction(
                            'doc["indexed_metadata.price"].value',
                            1.0
                        ))
                )
        );

        $this->assertResults(
            $result,
            ['3', '2', '1', '4', '5']
        );
    }

    /**
     * Score strategy composed with nested filter and sorting.
     */
    public function testScoreStrategyWithNested()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->filterBy('brand', 'brand.id', [1, 2, 3, 4])
                ->sortBy(
                    SortBy::create()
                        ->byValue(SortBy::SCORE)
                        ->byNestedField('brand.id', 'ASC')
                )
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createCustomFunction(
                            'doc["indexed_metadata.simple_int"].value',
                            1.0
                        ))
                )
        );

        $this->assertResults(
            $result,
            ['4', '1', '2', '3', '5']
        );
    }

    /**
     * Test decay.
     */
    public function testScoreStrategyDecay()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createDecayFunction(
                            ScoreStrategy::DECAY_GAUSS,
                            'relevance',
                            '0',
                            '45',
                            '10',
                            0.5
                        ))
                )
        );

        $this->assertResults(
            $result,
            ['2', '3', '1', '4', '5']
        );

        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createDecayFunction(
                            ScoreStrategy::DECAY_GAUSS,
                            'relevance',
                            '110',
                            '50',
                            '10',
                            0.5
                        ))
                )
        );

        $this->assertResults(
            $result,
            ['5', '{4', '1}', '3', '2']
        );
    }

    /**
     * Test several score strategies.
     */
    public function testSeveralScoreStrategies()
    {
        $result = $this->query(
            Query::createMatchAll()
                ->setScoreStrategies(
                    ScoreStrategies::createEmpty()
                        ->addScoreStrategy(ScoreStrategy::createFieldBoosting(
                            'relevance',
                            1.0,
                            1.0,
                            ScoreStrategy::MODIFIER_LN,
                            1.0
                        ))
                        ->addScoreStrategy(ScoreStrategy::createCustomFunction(
                            'doc["indexed_metadata.simple_int"].value',
                            1.0
                        ))
                        ->addScoreStrategy(ScoreStrategy::createDecayFunction(
                            ScoreStrategy::DECAY_GAUSS,
                            'relevance',
                            '110',
                            '50',
                            '10',
                            0.5,
                            50,
                            Filter::create('price', [2000], Filter::MUST_ALL, Filter::TYPE_FIELD)
                        ))
                )
        );

        $this->assertResults(
            $result,
            ['3', '4', '1', '2', '5']
        );
    }
}
