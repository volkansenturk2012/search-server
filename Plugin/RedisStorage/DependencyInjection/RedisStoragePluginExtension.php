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

namespace Apisearch\Plugin\RedisStorage\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class RedisStoragePluginExtension.
 */
class RedisStoragePluginExtension extends BaseExtension
{
    /**
     * Returns the recommended alias to use in XML.
     *
     * This alias is also the mandatory prefix to use when using YAML.
     *
     * @return string The alias
     */
    public function getAlias()
    {
        return 'apisearch_plugin_redis_storage';
    }

    /**
     * Return a new Configuration instance.
     *
     * If object returned by this method is an instance of
     * ConfigurationInterface, extension will use the Configuration to read all
     * bundle config definitions.
     *
     * Also will call getParametrizationValues method to load some config values
     * to internal parameters.
     *
     * @return ConfigurationInterface|null
     */
    protected function getConfigurationInstance(): ? ConfigurationInterface
    {
        return new RedisStoragePluginConfiguration($this->getAlias());
    }

    /**
     * Get the Config file location.
     *
     * @return string
     */
    protected function getConfigFilesLocation(): string
    {
        return __DIR__.'/../Resources/config';
    }

    /**
     * Config files to load.
     *
     * Each array position can be a simple file name if must be loaded always,
     * or an array, with the filename in the first position, and a boolean in
     * the second one.
     *
     * As a parameter, this method receives all loaded configuration, to allow
     * setting this boolean value from a configuration value.
     *
     * return array(
     *      'file1.yml',
     *      'file2.yml',
     *      ['file3.yml', $config['my_boolean'],
     *      ...
     * );
     *
     * @param array $config Config definitions
     *
     * @return array Config files
     */
    protected function getConfigFiles(array $config): array
    {
        return [
            'domain',
            'middlewares',
        ];
    }

    /**
     * Load Parametrization definition.
     *
     * return array(
     *      'parameter1' => $config['parameter1'],
     *      'parameter2' => $config['parameter2'],
     *      ...
     * );
     *
     * @param array $config Bundles config values
     *
     * @return array
     */
    protected function getParametrizationValues(array $config): array
    {
        $storageHost = $_SERVER['REDIS_STORAGE_HOST'] ?? $config['host'];
        if (null === $storageHost) {
            $exception = new InvalidConfigurationException('Please provide a host for redis storage plugin.');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'host'));

            throw $exception;
        }

        $storagePort = $_SERVER['REDIS_STORAGE_PORT'] ?? $config['port'];
        if (null === $storageHost) {
            $exception = new InvalidConfigurationException('Please provide a port for redis storage plugin.');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'port'));

            throw $exception;
        }

        return [
            'apisearch_plugin.redis_storage.locator_enabled' => $config['locator_enabled'],
            'apisearch_plugin.redis_storage.host' => (string) $storageHost,
            'apisearch_plugin.redis_storage.port' => (int) $storagePort,
            'apisearch_plugin.redis_storage.is_cluster' => (bool) ($_SERVER['REDIS_STORAGE_IS_CLUSTER'] ?? $config['is_cluster']),
            'apisearch_plugin.redis_storage.database' => (string) ($_SERVER['REDIS_STORAGE_DATABASE'] ?? $config['database']),
        ];
    }
}
