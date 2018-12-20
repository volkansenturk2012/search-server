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
use Apisearch\Exception\ResourceNotAvailableException;
use Apisearch\Model\IndexUUID;
use Apisearch\Plugin\Elastica\Domain\ElasticaWrapperWithRepositoryReference;
use Apisearch\Repository\RepositoryReference;
use Apisearch\Server\Domain\Repository\AppRepository\ConfigRepository as ConfigRepositoryInterface;

/**
 * Class ConfigRepository.
 */
class ConfigRepository extends ElasticaWrapperWithRepositoryReference implements ConfigRepositoryInterface
{
    /**
     * Config the index.
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
        $newRepositoryReference = $this
            ->getRepositoryReference()
            ->changeIndex($indexUUID);

        $this->writeCampaigns(
            $newRepositoryReference,
            $config
        );

        $this
            ->elasticaWrapper
            ->configureIndex(
                $newRepositoryReference,
                $config
            );

        $this->refresh();
    }

    /**
     * Write campaigns.
     *
     * @param RepositoryReference $repositoryReference
     * @param Config              $config
     */
    private function writeCampaigns(
        RepositoryReference $repositoryReference,
        Config $config
    ) {
        $campaigns = $config
            ->getCampaigns()
            ->toArray();

        $filePath = $this->getConfigPath($repositoryReference).'/campaigns.json';
        if (empty($campaigns)) {
            file_exists($filePath)
                ? unlink($filePath)
                : false;

            return;
        }

        $fileHandle = fopen($filePath, 'w');
        fwrite($fileHandle, json_encode($config->getCampaigns()->toArray()));
        fclose($fileHandle);
    }
}
