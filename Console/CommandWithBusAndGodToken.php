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
use Apisearch\Model\AppUUID;
use Apisearch\Model\Token;
use Apisearch\Model\TokenUUID;
use Exception;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class CommandWithBusAndGodToken.
 */
abstract class CommandWithBusAndGodToken extends ApisearchCommand
{
    /**
     * @var CommandBus
     *
     * Message bus
     */
    protected $commandBus;

    /**
     * @var string
     *
     * God token
     */
    protected $godToken;

    /**
     * Controller constructor.
     *
     * @param CommandBus $commandBus
     * @param string     $godToken
     */
    public function __construct(
        CommandBus $commandBus,
        string $godToken
    ) {
        parent::__construct();

        $this->commandBus = $commandBus;
        $this->godToken = $godToken;
    }

    /**
     * Create token instance.
     *
     * @param TokenUUID $tokenUUID
     * @param AppUUID   $appUUID
     *
     * @return Token
     */
    protected function createToken(
        TokenUUID $tokenUUID,
        AppUUID $appUUID
    ): Token {
        return new Token(
            $tokenUUID,
            $appUUID
        );
    }

    /**
     * Create god token instance.
     *
     * @param AppUUID $appUUID
     *
     * @return Token
     */
    protected function createGodToken(AppUUID $appUUID): Token
    {
        return $this->createToken(
            TokenUUID::createById($this->godToken),
            $appUUID
        );
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return int|null null or 0 if everything went fine, or an error code
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->startCommand($output);
        $result = null;
        try {
            $result = $this->dispatchDomainEvent(
                $input,
                $output
            );
            if (!empty($this->getSuccessMessage($input, $result))) {
                $this->printMessage(
                    $output,
                    $this->getHeader(),
                    $this->getSuccessMessage($input, $result)
                );
            }
        } catch (Exception $e) {
            $this->printMessageFail(
                $output,
                $this->getHeader(),
                $e->getMessage()
            );
        }

        $this->finishCommand($output);

        return is_int($result)
            ? $result
            : 0;
    }

    /**
     * Dispatch domain event.
     *
     * @return string
     */
    abstract protected function getHeader(): string;

    /**
     * Dispatch domain event.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return mixed|null
     */
    abstract protected function dispatchDomainEvent(InputInterface $input, OutputInterface $output);

    /**
     * Get success message.
     *
     * @param InputInterface $input
     * @param mixed          $result
     *
     * @return string
     */
    abstract protected function getSuccessMessage(
        InputInterface $input,
        $result
    ): string;
}
