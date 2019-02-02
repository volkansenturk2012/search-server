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

namespace Apisearch\Plugin\RedisQueue\DependencyInjection;

use Apisearch\Server\DependencyInjection\Env;
use Apisearch\Server\Domain\Consumer\ConsumerManager;
use Mmoreram\BaseBundle\DependencyInjection\BaseExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class RedisQueuePluginExtension.
 */
class RedisQueuePluginExtension extends BaseExtension
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
        return 'apisearch_plugin_rsqueue';
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
        return new RedisQueuePluginConfiguration($this->getAlias());
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
            'console',
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
        $redisHost = Env::get('REDIS_QUEUE_HOST', $config['host']);
        if (null === $redisHost) {
            $exception = new InvalidConfigurationException('Please provide a host for the rs queue plugin');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'host'));

            throw $exception;
        }

        $redisPort = Env::get('REDIS_QUEUE_PORT', $config['port']);
        if (null === $redisPort) {
            $exception = new InvalidConfigurationException('Please provide a port for the rs queue plugin');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'port'));

            throw $exception;
        }

        return [
            'apisearch_plugin.redis_queue.host' => (string) $redisHost,
            'apisearch_plugin.redis_queue.port' => (int) $redisPort,
            'apisearch_plugin.redis_queue.is_cluster' => (bool) Env::get('REDIS_QUEUE_IS_CLUSTER', $config['is_cluster']),
            'apisearch_plugin.redis_queue.database' => (string) Env::get('REDIS_QUEUE_DATABASE', $config['database']),
            'apisearch_plugin.redis_queue.seconds_to_wait_on_busy' => (int) Env::get('REDIS_QUEUE_SECONDS_TO_WAIT_ON_BUSY', $config['seconds_to_wait_on_busy']),
            'apisearch_plugin.redis_queue.queues' => [
                'queues' => [
                    ConsumerManager::COMMAND_CONSUMER_TYPE => Env::get('COMMANDS_QUEUE_NAME', $config['commands_queue_name']),
                    ConsumerManager::DOMAIN_EVENT_CONSUMER_TYPE => Env::get('DOMAIN_EVENTS_QUEUE_NAME', $config['events_queue_name']),
                ],
                'busy_queues' => [
                    ConsumerManager::COMMAND_CONSUMER_TYPE => Env::get('COMMANDS_BUSY_QUEUE_NAME', $config['commands_busy_queue_name']),
                    ConsumerManager::DOMAIN_EVENT_CONSUMER_TYPE => Env::get('DOMAIN_EVENTS_BUSY_QUEUE_NAME', $config['events_busy_queue_name']),
                ],
            ],
        ];
    }
}
