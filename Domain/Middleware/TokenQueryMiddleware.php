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

namespace Apisearch\Server\Domain\Middleware;

use Apisearch\Query\Query as QueryModel;
use Apisearch\Server\Domain\Model\QueryMerger;
use Apisearch\Server\Domain\Query\Query;
use League\Tactician\Middleware;

/**
 * Class TokenQueryMiddleware.
 */
class TokenQueryMiddleware implements Middleware
{
    /**
     * @var int
     *
     * Number of results limitation
     */
    private $numberOfResultsLimitation;

    /**
     * TokenQueryMiddleware constructor.
     *
     * @param int $numberOfResultsLimitation
     */
    public function __construct(int $numberOfResultsLimitation)
    {
        $this->numberOfResultsLimitation = $numberOfResultsLimitation;
    }

    /**
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $isQuery = $command instanceof Query;

        if ($isQuery) {
            $token = $command->getToken();
            $parameters = $command->getParameters();
            $queryAsArray = $command
                ->getQuery()
                ->toArray();

            $queriesAsArray = [
                [$token->getMetadataValue('base_query', []), QueryMerger::BASE],
                [$token->getMetadataValue('merge_query', []), QueryMerger::MERGE],
                [$token->getMetadataValue('force_query', []), QueryMerger::FORCE],
            ];

            foreach ($queriesAsArray as list($mergeableQuery, $type)) {
                $queryAsArray = QueryMerger::mergeQueries(
                    $queryAsArray,
                    $mergeableQuery,
                    $type
                );
            }

            if (($queryAsArray['size'] ?? QueryModel::DEFAULT_SIZE) > $this->numberOfResultsLimitation) {
                $queryAsArray['size'] = $this->numberOfResultsLimitation;
            }

            if (!empty($parameters)) {
                $queryJson = json_encode($queryAsArray);
                $queryJson = preg_replace_callback('~\{\{.*?\}\}~', function (array $matches) use ($parameters) {
                    $key = ltrim(rtrim($matches[0], '}'), '{');

                    return $parameters[$key] ?? '';
                }, $queryJson);
                $queryAsArray = json_decode($queryJson, true);
            }

            $command = new Query(
                $command->getRepositoryReference(),
                $token,
                QueryModel::createFromArray($queryAsArray)
            );
        }

        return $next($command);
    }
}
