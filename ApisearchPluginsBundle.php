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

namespace Apisearch\Server;

use Apisearch\Plugin\Callbacks\Domain\Callbacks;
use Apisearch\Plugin\Elastica\ElasticaPluginBundle;
use Apisearch\Plugin\ELK\ELKPluginBundle;
use Apisearch\Plugin\MostRelevantWords\MostRelevantWordsPluginBundle;
use Apisearch\Plugin\Multilanguage\MultilanguagePluginBundle;
use Apisearch\Plugin\NewRelic\NewRelicPluginBundle;
use Apisearch\Plugin\RedisMetadataFields\RedisMetadataFieldsPluginBundle;
use Apisearch\Plugin\RedisStorage\RedisStoragePluginBundle;
use Apisearch\Plugin\RSQueue\RSQueuePluginBundle;
use Apisearch\Plugin\StaticTokens\StaticTokensPluginBundle;
use Apisearch\Server\DependencyInjection\Env;
use Apisearch\Server\Domain\Plugin\Plugin;
use Mmoreram\BaseBundle\BaseBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ApisearchPluginsBundle.
 */
class ApisearchPluginsBundle extends BaseBundle
{
    /**
     * Return all bundle dependencies.
     *
     * Values can be a simple bundle namespace or its instance
     *
     * @return array
     */
    public static function getBundleDependencies(KernelInterface $kernel): array
    {
        $pluginsAsString = Env::get('APISEARCH_ENABLED_PLUGINS', '');
        $pluginsAsArray = explode(',', $pluginsAsString);
        $pluginsAsArray = array_map('trim', $pluginsAsArray);
        $pluginsAsArray = self::resolveAliases($pluginsAsArray);

        $pluginsAsArray = array_filter($pluginsAsArray, function (string $pluginNamespace) {
            if (
                empty($pluginNamespace) ||
                !class_exists($pluginNamespace)
            ) {
                return false;
            }

            $reflectionClass = new \ReflectionClass($pluginNamespace);

            return $reflectionClass->implementsInterface(Plugin::class);
        });

        return $pluginsAsArray;
    }

    /**
     * Resolve aliases.
     *
     * @param array $bundles
     *
     * @return array
     */
    private static function resolveAliases(array $bundles): array
    {
        $aliases = [
            'callbacks' => Callbacks::class,
            'elastica' => ElasticaPluginBundle::class,
            'elk' => ELKPluginBundle::class,
            'most_relevant_words' => MostRelevantWordsPluginBundle::class,
            'multilanguage' => MultilanguagePluginBundle::class,
            'newrelic' => NewRelicPluginBundle::class,
            'redis_metadata_fields' => RedisMetadataFieldsPluginBundle::class,
            'redis_storage' => RedisStoragePluginBundle::class,
            'rsqueue' => RSQueuePluginBundle::class,
            'static_tokens' => StaticTokensPluginBundle::class,
        ];

        $combined = array_combine(
            array_values($bundles),
            array_values($bundles)
        );

        return array_values(
            array_replace(
                $combined,
                array_intersect_key(
                    $aliases,
                    $combined
                )
            )
        );
    }
}
