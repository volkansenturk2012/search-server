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

namespace Apisearch\Plugin\StaticTokens\Tests\Functional;

use Apisearch\Http\Endpoints;
use Apisearch\Plugin\StaticTokens\StaticTokensPluginBundle;
use Apisearch\Server\Tests\Functional\HttpFunctionalTest;

/**
 * Class StaticTokensFunctionalTest.
 */
abstract class StaticTokensFunctionalTest extends HttpFunctionalTest
{
    /**
     * Decorate bundles.
     *
     * @param array $bundles
     *
     * @return array
     */
    protected static function decorateBundles(array $bundles): array
    {
        $bundles[] = StaticTokensPluginBundle::class;

        return $bundles;
    }

    /**
     * @param array $configuration
     *
     * @return array
     */
    protected static function decorateConfiguration(array $configuration): array
    {
        $configuration['apisearch_plugin_static_tokens']['tokens'] = [
            'blablabla' => [
                'app_id' => self::$appId,
            ],
            'onlyindex' => [
                'app_id' => self::$appId,
                'indices' => [
                    self::$index,
                ],
            ],
            'onlyaddtoken' => [
                'app_id' => self::$appId,
                'endpoints' => Endpoints::compose(Endpoints::queryOnly()),
            ],
            'base_filtered_token' => [
                'app_id' => self::$appId,
                'metadata' => [
                    'base_query' => ['q' => 'Matutano'],
                ],
            ],
        ];

        return $configuration;
    }

    /**
     * Save events.
     *
     * @return bool
     */
    protected static function saveEvents(): bool
    {
        return false;
    }
}
