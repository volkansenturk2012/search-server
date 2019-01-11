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

namespace Apisearch\Server\Domain\Token;

/**
 * Class TokenLocators.
 */
class TokenLocators
{
    /**
     * @var TokenLocator[]
     *
     * Token locators
     */
    private $tokenLocators = [];

    /**
     * Add token locator.
     *
     * @param TokenLocator $tokenLocator
     */
    public function addTokenLocator(TokenLocator $tokenLocator)
    {
        $this->tokenLocators[] = $tokenLocator;
    }

    /**
     * Get valid token locators.
     *
     * @return TokenLocator[]
     */
    public function getValidTokenLocators(): array
    {
        $tokenLocators = [];
        foreach ($this->tokenLocators as $tokenLocator) {
            if (!$tokenLocator->isValid()) {
                continue;
            }

            $tokenLocators[] = $tokenLocator;
        }

        return $tokenLocators;
    }
}
