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

namespace Apisearch\Server\Domain\Plugin;

use League\Tactician\Middleware;

/**
 * Class PluginMiddleware.
 */
class PluginMiddlewareCollector implements Middleware
{
    /**
     * @var PluginMiddleware[]
     *
     * Plugin middleware
     */
    private $pluginMiddlewares = ['_all' => []];

    /**
     * Add plugin middleware.
     *
     * @param PluginMiddleware $pluginMiddleware
     */
    public function addPluginMiddleware(PluginMiddleware $pluginMiddleware)
    {
        $commandNamespaces = $pluginMiddleware->getSubscribedCommands();

        if (empty($commandNamespaces)) {
            $this->pluginMiddlewares['_all'][] = $pluginMiddleware;

            return;
        }

        foreach ($commandNamespaces as $commandNamespace) {
            if (!isset($this->pluginMiddlewares[$commandNamespace])) {
                $this->pluginMiddlewares[$commandNamespace] = [];
            }

            $this->pluginMiddlewares[$commandNamespace][] = $pluginMiddleware;
        }
    }

    /**
     * Get PluginMiddlewares.
     *
     * @return PluginMiddleware[]
     */
    public function getPluginMiddlewares(): array
    {
        return $this->pluginMiddlewares;
    }

    /**
     * @param object   $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute($command, callable $next)
    {
        $lastCallable = $next;
        $middlewares = $this->pluginMiddlewares['_all'];
        foreach ($this->getNamespaceCollectionOfClass($command) as $namespace) {
            if (isset($this->pluginMiddlewares[$namespace])) {
                $middlewares = array_merge(
                    $middlewares,
                    $this->pluginMiddlewares[$namespace]
                );
            }
        }

        if (!empty($middlewares)) {
            /*
             * @var PluginMiddleware
             */
            foreach ($middlewares as $pluginMiddleware) {
                $lastCallable = function ($command) use ($pluginMiddleware, $lastCallable) {
                    return $pluginMiddleware->execute(
                        $command,
                        $lastCallable
                    );
                };
            }
        }

        return $lastCallable($command);
    }

    /**
     * Return class namespace, all parent namespaces and interfaces of a class.
     *
     * @param object $object
     *
     * @return string[]
     */
    private function getNamespaceCollectionOfClass($object): array
    {
        return array_merge(
            [get_class($object)],
            class_parents($object, false),
            class_implements($object, false)
        );
    }
}
