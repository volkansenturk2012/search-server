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

namespace Apisearch\Plugin\Elastica\Domain\Repository;

use Apisearch\Model\Item;
use Apisearch\Model\ItemUUID;
use Apisearch\Plugin\Elastica\Domain\Builder\QueryBuilder;
use Apisearch\Plugin\Elastica\Domain\Builder\ResultBuilder;
use Apisearch\Plugin\Elastica\Domain\ElasticaWrapperWithRepositoryReference;
use Apisearch\Plugin\Elastica\Domain\ItemElasticaWrapper;
use Apisearch\Query\Query;
use Apisearch\Result\Result;
use Apisearch\Server\Domain\Repository\Repository\QueryRepository as QueryRepositoryInterface;
use Elastica\Query as ElasticaQuery;
use Elastica\Suggest;

/**
 * Class QueryRepository.
 */
class QueryRepository extends ElasticaWrapperWithRepositoryReference implements QueryRepositoryInterface
{
    /**
     * @var QueryBuilder
     *
     * Query builder
     */
    private $queryBuilder;

    /**
     * @var ResultBuilder
     *
     * Result builder
     */
    private $resultBuilder;

    /**
     * ElasticaSearchRepository constructor.
     *
     * @param ItemElasticaWrapper $elasticaWrapper
     * @param string              $repositoryConfigPath
     * @param bool                $refreshOnWrite
     * @param QueryBuilder        $queryBuilder
     * @param ResultBuilder       $resultBuilder
     */
    public function __construct(
        ItemElasticaWrapper $elasticaWrapper,
        string $repositoryConfigPath,
        bool $refreshOnWrite,
        QueryBuilder $queryBuilder,
        ResultBuilder $resultBuilder
    ) {
        parent::__construct(
            $elasticaWrapper,
            $repositoryConfigPath,
            $refreshOnWrite
        );

        $this->queryBuilder = $queryBuilder;
        $this->resultBuilder = $resultBuilder;
    }

    /**
     * Search cross the index types.
     *
     * @param Query $query
     *
     * @return Result
     */
    public function query(Query $query): Result
    {
        $mainQuery = new ElasticaQuery();
        $boolQuery = new ElasticaQuery\BoolQuery();
        $this
            ->queryBuilder
            ->buildQuery(
                $query,
                $mainQuery,
                $boolQuery
            );

        $this->promoteUUIDs(
            $boolQuery,
            $query->getItemsPromoted()
        );

        if ($query->areHighlightEnabled()) {
            $this->addHighlights($mainQuery);
        }

        $this->addSuggest(
            $mainQuery,
            $query
        );

        $mainQuery->setExplain(false);
        $results = $this
            ->elasticaWrapper
            ->search(
                $this->getRepositoryReference(),
                $mainQuery,
                $query->areResultsEnabled()
                    ? $query->getFrom()
                    : 0,
                $query->areResultsEnabled()
                    ? $query->getSize()
                    : 0
            );

        return $this->elasticaResultToResult(
            $query,
            $results
        );
    }

    /**
     * Build a Result object given elastica result object.
     *
     * @param Query $query
     * @param array $elasticaResults
     *
     * @return Result
     */
    private function elasticaResultToResult(
        Query $query,
        array $elasticaResults
    ): Result {
        $resultAggregations = [];

        /*
         * Build Result instance
         */
        if (
            $query->areAggregationsEnabled() &&
            isset($elasticaResults['aggregations']['all'])
        ) {
            $resultAggregations = $elasticaResults['aggregations']['all']['universe'];
            unset($resultAggregations['common']);

            $result = new Result(
                $query,
                $elasticaResults['aggregations']['all']['universe']['doc_count'],
                $elasticaResults['total_hits']
            );
        } else {
            $result = new Result(
                $query,
                0,
                $elasticaResults['total_hits']
            );
        }

        /*
         * @var ElasticaResult
         */
        foreach ($elasticaResults['results'] as $elasticaResult) {
            $source = $elasticaResult->getSource();

            if (
                isset($elasticaResult->getParam('sort')[0]) &&
                is_float($elasticaResult->getParam('sort')[0])
            ) {
                $source['distance'] = $elasticaResult->getParam('sort')[0];
            }

            $item = Item::createFromArray($source);
            $score = $elasticaResult->getScore();
            $item->setScore(is_float($score)
                ? $score
                : 1
            );

            if ($query->areHighlightEnabled()) {
                $formedHighlights = [];
                foreach ($elasticaResult->getHighlights() as $highlightField => $highlightValue) {
                    $formedHighlights[str_replace('searchable_metadata.', '', $highlightField)] = $highlightValue[0];
                }

                $item->setHighlights($formedHighlights);
            }

            $result->addItem($item);
        }

        if (
            $query->areAggregationsEnabled() &&
            isset($resultAggregations['doc_count'])
        ) {
            $result->setAggregations(
                $this
                    ->resultBuilder
                    ->buildResultAggregations(
                        $query,
                        $resultAggregations
                    )
            );
        }

        /*
         * Build suggests
         */
        if (isset($elasticaResults['suggests']['completion']) && $query->areSuggestionsEnabled()) {
            foreach ($elasticaResults['suggests']['completion'][0]['options'] as $suggest) {
                $result->addSuggest($suggest['text']);
            }
        }

        return $result;
    }

    /**
     * Add suggest into an Elastica Query.
     *
     * @param ElasticaQuery $mainQuery
     * @param Query         $query
     */
    private function addSuggest($mainQuery, $query)
    {
        if ($query->areSuggestionsEnabled()) {
            $completitionText = new Suggest\Completion(
                'completion',
                'suggest'
            );
            $completitionText->setText($query->getQueryText());

            $mainQuery->setSuggest(
                new Suggest($completitionText)
            );
        }
    }

    /**
     * Promote UUID.
     *
     * The boosting values go from 1 (not included) to 3 (not included)
     *
     * @param ElasticaQuery\BoolQuery $boolQuery
     * @param ItemUUID[]              $itemsPriorized
     */
    private function promoteUUIDs(
        ElasticaQuery\BoolQuery $boolQuery,
        array $itemsPriorized
    ) {
        if (empty($itemsPriorized)) {
            return;
        }

        $it = count($itemsPriorized);
        foreach ($itemsPriorized as $position => $itemUUID) {
            $boolQuery->addShould(new ElasticaQuery\Term([
                '_id' => [
                    'value' => $itemUUID->composeUUID(),
                    'boost' => 10 + ($it-- / (count($itemsPriorized) + 1)),
                ],
            ]));
        }
    }

    /**
     * Highlight.
     *
     * @param ElasticaQuery $query
     */
    private function addHighlights(ElasticaQuery $query)
    {
        $query->setHighlight([
            'fields' => [
                '*' => [
                    'fragment_size' => 100,
                    'number_of_fragments' => 3,
                ],
            ],
        ]);
    }
}
