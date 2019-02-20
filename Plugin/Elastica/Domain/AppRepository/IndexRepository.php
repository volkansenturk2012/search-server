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

namespace Apisearch\Plugin\Elastica\Domain\AppRepository;

use Apisearch\Config\Config;
use Apisearch\Exception\ResourceExistsException;
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\Index;
use Apisearch\Model\IndexUUID;
use Apisearch\Plugin\Elastica\Domain\ElasticaWrapperWithRepositoryReference;
use Apisearch\Server\Domain\Repository\AppRepository\IndexRepository as IndexRepositoryInterface;
use Elastica\Index\Stats;

/**
 * Class IndexRepository.
 */
class IndexRepository extends ElasticaWrapperWithRepositoryReference implements IndexRepositoryInterface
{
    /**
     * Get indices.
     *
     * @return Index[]
     */
    public function getIndices(): array
    {
        return $this
            ->elasticaWrapper
            ->getIndices($this->getRepositoryReference());
    }

    /**
     * Create an index.
     *
     * @param IndexUUID $indexUUID
     * @param Config    $config
     *
     * @throws ResourceExistsException
     */
    public function createIndex(
        IndexUUID $indexUUID,
        Config $config
    ) {
        $newRepositoryReference = $this
            ->getRepositoryReference()
            ->changeIndex($indexUUID);

        $this
            ->elasticaWrapper
            ->createIndex(
                $newRepositoryReference,
                $config
            );

        $this
            ->elasticaWrapper
            ->createIndexMapping(
                $newRepositoryReference,
                $config
            );

        $this->refresh();
    }

    /**
     * Delete the index.
     *
     * @param IndexUUID $indexUUID
     */
    public function deleteIndex(IndexUUID $indexUUID)
    {
        $this
            ->elasticaWrapper
            ->deleteIndex($this
                ->getRepositoryReference()
                ->changeIndex($indexUUID)
            );
    }

    /**
     * Reset the index.
     *
     * @param IndexUUID $indexUUID
     */
    public function resetIndex(IndexUUID $indexUUID)
    {
        $this
            ->elasticaWrapper
            ->resetIndex($this
                ->getRepositoryReference()
                ->changeIndex($indexUUID)
            );

        $this->refresh();
    }

    /**
     * Configure the index.
     *
     * @param IndexUUID $indexUUID
     * @param Config    $config
     *
     * @throws ResourceNotAvailableException
     */
    public function configureIndex(
        IndexUUID $indexUUID,
        Config $config
    ) {
        $this
            ->elasticaWrapper
            ->configureIndex(
                $this
                    ->getRepositoryReference()
                    ->changeIndex($indexUUID),
                $config
            );

        $this->refresh();
    }

    /**
     * Get the index stats.
     *
     * @param IndexUUID $indexUUID
     *
     * @return Stats
     */
    public function getIndexStats(IndexUUID $indexUUID): Stats
    {
        return $this
            ->elasticaWrapper
            ->getIndexStats($this
                ->getRepositoryReference()
                ->changeIndex($indexUUID)
            );
    }
}
