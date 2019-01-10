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

namespace Apisearch\Plugin\RabbitMQ\DependencyInjection;

use Apisearch\Server\DependencyInjection\Env;
use Mmoreram\BaseBundle\DependencyInjection\BaseExtension;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * Class RabbitMQPluginExtension.
 */
class RabbitMQPluginExtension extends BaseExtension
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
        return 'apisearch_plugin_rabbitmq';
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
        return new RabbitMQPluginConfiguration($this->getAlias());
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
        $rabbitmqHost = Env::get('RABBITMQ_QUEUE_HOST', $config['host']);
        if (null === $rabbitmqHost) {
            $exception = new InvalidConfigurationException('Please provide a host for the rabbitmq plugin');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'host'));

            throw $exception;
        }

        $rabbitmqPort = Env::get('RABBITMQ_QUEUE_PORT', $config['port']);
        if (null === $rabbitmqPort) {
            $exception = new InvalidConfigurationException('Please provide a port for the rabbitmq plugin');
            $exception->setPath(sprintf('%s.%s', $this->getAlias(), 'port'));

            throw $exception;
        }

        return [
            'apisearch_plugin.rabbitmq.host' => (string) $rabbitmqHost,
            'apisearch_plugin.rabbitmq.port' => (int) $rabbitmqPort,
            'apisearch_plugin.rabbitmq.user' => (string) Env::get('RABBITMQ_QUEUE_USER', $config['user']),
            'apisearch_plugin.rabbitmq.password' => (string) Env::get('RABBITMQ_QUEUE_PASSWORD', $config['password']),
            'apisearch_plugin.rabbitmq.vhost' => (string) Env::get('RABBITMQ_QUEUE_VHOST', $config['vhost']),
            'apisearch_plugin.rabbitmq.commands_queue_name' => Env::get('COMMANDS_QUEUE_NAME', $config['commands_queue_name']),
            'apisearch_plugin.rabbitmq.events_queue_name' => Env::get('EVENTS_QUEUE_NAME', $config['events_queue_name']),
            'apisearch_plugin.rabbitmq.busy_queue_name' => Env::get('BUSY_QUEUE_NAME', $config['busy_queue_name']),
        ];
    }
}
