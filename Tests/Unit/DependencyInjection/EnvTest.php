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

namespace Apisearch\Tests\Server\DependencyInjection;

use Apisearch\Server\DependencyInjection\Env;
use PHPUnit\Framework\TestCase;

/**
 * Class EnvTest.
 */
class EnvTest extends TestCase
{
    /**
     * Test.
     */
    public function testDefaultBehavior()
    {
        $_ENV['AAA'] = 'A1';
        $_ENV['BBB'] = 'B1';
        $_ENV['EEE'] = '';
        $_SERVER['AAA'] = 'A2';
        $_SERVER['CCC'] = 'C2';
        $_SERVER['FFF'] = '';
        $this->assertEquals('A1', Env::get('AAA', 'A3'));
        $this->assertEquals('B1', Env::get('BBB', 'B3'));
        $this->assertEquals('C2', Env::get('CCC', 'C3'));
        $this->assertEquals('D3', Env::get('DDD', 'D3'));
        $this->assertEquals('', Env::get('EEE', 'E3'));
        $this->assertEquals('', Env::get('FFF', 'E3'));
        $this->assertEquals('', Env::get('HHH', ''));
    }
}
