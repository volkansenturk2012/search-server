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

namespace Apisearch\Server\Console;

use Apisearch\Command\ApisearchCommand;
use Apisearch\Server\Domain\Plugin\Plugin;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class ServerConfigurationCommand.
 */
class ServerConfigurationCommand extends ApisearchCommand
{
    /**
     * @var KernelInterface
     *
     * Kernel
     */
    private $kernel;

    /**
     * Kernel.
     *
     * @param KernelInterface $kernel
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();

        $this->kernel = $kernel;
    }

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Print server configuration');
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    protected function getHeader(): string
    {
        return 'Server';
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return null|int null or 0 if everything went fine, or an error code
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startCommand($output);
        $this->printApisearchServer($output);
        $this->printMessage($output, '##', 'Server started');
        $this->printInfoMessage($output, '##', ' ~~ with');
        $this->printInfoMessage($output, '##', sprintf(' ~~ --env = %s', $this->kernel->getEnvironment()));
        foreach ($_SERVER as $item => $value) {
            if (!is_string($value)) {
                continue;
            }

            $this->printInfoMessage($output, '##', sprintf(' ~~ %s = %s', $item, $value));
        }
        $this->printInfoMessage($output, '##', '');
        $this->printInfoMessage($output, '##', 'Loaded plugins');

        $enabledPlugins = array_filter($this->kernel->getBundles(), function (BundleInterface $bundle) {
            return $bundle instanceof Plugin;
        });

        $enabledPluginsName = array_map(function (Plugin $plugin) {
            return $plugin->getPluginName();
        }, $enabledPlugins);

        foreach ($enabledPluginsName as $enabledPluginName) {
            $this->printInfoMessage($output, '##', sprintf(' ~~ %s', $enabledPluginName));
        }
        $this->printSystemMessage($output, '##', '');
    }

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string {
        return '';
    }

    /**
     * @param OutputInterface $output
     */
    private function printApisearchServer(OutputInterface $output)
    {
        $logo = '
     _____                                            _
    (  _  )        _                                 ( )
    | (_) | _ _   (_)  ___    __     _ _  _ __   ___ | |__
    |  _  |( \'_`\ | |/\',__) /\'__`\ /\'_` )( \'__)/\'___)|  _ `\
    | | | || (_) )| |\__, \(  ___/( (_| || |  ( (___ | | | |
    (_) (_)| ,__/\'(_)(____/`\____)`\__,_)(_)  `\____)(_) (_)
           | |
           (_)
        ';

        $output->writeln($logo);
    }
}
