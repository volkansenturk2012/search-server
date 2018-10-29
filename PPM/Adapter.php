<?php
/*
 * This file is part of the {Package name}.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Feel free to edit as you please, and have fun.
 *
 * @author Marc Morera <yuhu@mmoreram.com>
 */

namespace Apisearch\Server\PPM;

use OneBundleApp\App\AppFactory;
use PHPPM\Bootstraps\ApplicationEnvironmentAwareInterface;
use PHPPM\Bootstraps\BootstrapInterface;

/**
 * Class Adapter
 */
class Adapter implements BootstrapInterface, ApplicationEnvironmentAwareInterface
{
    /**
     * @var string
     *
     * Environment
     */
    protected $environment;

    /**
     * @var boolean
     *
     * Debug
     */
    protected $debug;

    /**
     * Instantiate the bootstrap, storing the $appenv
     *
     * @param string $environment
     * @param boolean $debug
     */
    public function initialize($environment, $debug)
    {
        $this->environment = $environment;
        $this->debug = $debug;
    }

    public function getApplication()
    {
        $appPath = __DIR__ . '/..';
        return AppFactory::createApp(
            $appPath,
            $this->environment,
            $this->debug
        );
    }
}