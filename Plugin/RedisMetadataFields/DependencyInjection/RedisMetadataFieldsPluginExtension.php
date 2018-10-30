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

namespace Apisearch\Plugin\RedisMetadataFields\DependencyInjection;

use Mmoreram\BaseBundle\DependencyInjection\BaseExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class RedisMetadataFieldsPluginExtension.
 */
class RedisMetadataFieldsPluginExtension extends BaseExtension
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
        return 'apisearch_plugin_redis_metadata_fields';
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
        return new RedisMetadataFieldsPluginConfiguration($this->getAlias());
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
            'middleware',
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
        $storageHost = $_SERVER['REDIS_METADATA_FIELDS_HOST'] ?? $config['host'];
        if (null === $storageHost) {
            $exception = new InvalidConfigurationException('Please provide a host for redis metadata fields plugin.');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'host'));

            throw $exception;
        }

        $storagePort = $_SERVER['REDIS_METADATA_FIELDS_PORT'] ?? $config['port'];
        if (null === $storageHost) {
            $exception = new InvalidConfigurationException('Please provide a port for redis metadata fields plugin.');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'port'));

            throw $exception;
        }

        return [
            'apisearch_plugin.redis_metadata_fields.host' => (string) $storageHost,
            'apisearch_plugin.redis_metadata_fields.port' => (int) $storagePort,
            'apisearch_plugin.redis_metadata_fields.is_cluster' => (bool) ($_SERVER['REDIS_METADATA_FIELDS_IS_CLUSTER'] ?? $config['is_cluster']),
            'apisearch_plugin.redis_metadata_fields.database' => (string) ($_SERVER['REDIS_METADATA_FIELDS_DATABASE'] ?? $config['database']),
            'apisearch_plugin.redis_metadata_fields.key' => (string) ($_SERVER['REDIS_METADATA_FIELDS_KEY'] ?? $config['key']),
        ];
    }
}
