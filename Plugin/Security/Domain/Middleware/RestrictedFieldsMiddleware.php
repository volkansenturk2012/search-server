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

namespace Apisearch\Plugin\Security\Domain\Middleware;

use Apisearch\Server\Domain\Plugin\PluginMiddleware;
use Apisearch\Server\Domain\Query\Query;

/**
 * Class RestrictedFieldsMiddleware.
 */
class RestrictedFieldsMiddleware implements PluginMiddleware
{
    /**
     * Execute middleware.
     *
     * @param mixed    $command
     * @param callable $next
     *
     * @return mixed
     */
    public function execute(
        $command,
        $next
    ) {
        /**
         * @var Query
         */
        $token = $command->getToken();
        $query = $command->getQuery();
        $restrictedFields = $token->getMetadataValue('restricted_fields', []);
        $allowedFields = $token->getMetadataValue('allowed_fields', []);
        $fields = $query->getFields();

        foreach ($restrictedFields as $restrictedField) {
            $fields[] = '!'.$restrictedField;
        }

        foreach ($allowedFields as $allowedField) {
            $fields[] = $allowedField;
        }

        $query->setFields($fields);

        return $next($command);
    }

    /**
     * Commands subscribed namespace. Can refer to specific class namespace, any
     * parent class or any interface.
     *
     * By returning an empty array, means coupled to all.
     *
     * @return string[]
     */
    public function getSubscribedCommands(): array
    {
        return [Query::class];
    }
}
