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

namespace Apisearch\Plugin\RabbitMQ\Tests\Functional;

use Apisearch\Plugin\RabbitMQ\RabbitMQPluginBundle;
use Apisearch\Server\Tests\Functional\Domain\Repository\AsynchronousCommandsTest as BaseAsynchronousCommandsTest;

/**
 * Class AsynchronousCommandsTest.
 */
class AsynchronousCommandsTest extends BaseAsynchronousCommandsTest
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
        $bundles = parent::decorateBundles($bundles);
        $bundles[] = RabbitMQPluginBundle::class;

        return $bundles;
    }
}
