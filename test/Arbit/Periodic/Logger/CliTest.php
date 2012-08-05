<?php
/**
 * This file is part of Periodic.
 *
 * Periodic is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; version 3 of the License.
 *
 * Periodic is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
 * License for * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Periodic; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Periodic
 * @subpackage Command
 * @version $Revision: 1006 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\Periodic\Logger;

use Arbit\Periodic\TestCase,
    Arbit\Periodic\Logger;

require_once __DIR__ . '/../TestCase.php';

class CliTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    protected function logSomething( Logger $logger )
    {
        $logger->log( 'Info 1' );
        $logger->setTask( 'task1' );
        $logger->log( 'Info 2' );
        $logger->setCommand( 'command1' );
        $logger->log( 'Info 3' );
        $logger->setTask();
        $logger->log( 'Warning', Logger::WARNING );
        $logger->log( 'Error', Logger::ERROR );
    }

    protected function getLoggerMock( array $messages )
    {
        $logger = $this->getMock( '\\Arbit\\Periodic\\Logger\\Cli', array( 'write' ) );

        foreach ( $messages as $nr => $message )
        {
            $logger
                ->expects( $this->at( $nr ) )
                ->method( 'write' )
                ->with(
                    $this->equalTo( $message[0] ),
                    $this->matchesRegularExpression(
                        '(^\\[[^\\]]+\\] ' . preg_quote( $message[1] ) . ')'
                    )
                );
        }

        return $logger;
    }

    public function testLogInfo()
    {
        $logger = $this->getLoggerMock( array(
            array( 'php://stdout', 'Info: Info 1' ),
        ) );

        $logger->log( 'Info 1' );
    }

    public function testLogWarning()
    {
        $logger = $this->getLoggerMock( array(
            array( 'php://stderr', 'Warning: Warning 1' ),
        ) );

        $logger->log( 'Warning 1', Logger::WARNING );
    }

    public function testLogError()
    {
        $logger = $this->getLoggerMock( array(
            array( 'php://stderr', 'Error: Error 1' ),
        ) );

        $logger->log( 'Error 1', Logger::ERROR );
    }

    public function testRemappedLogging()
    {
        $logger = $this->getLoggerMock( array(
            array( 'php://stdout', 'Warning: Warning' ),
            array( 'php://stdout', 'Error: Error' ),
        ) );

        $logger->setMapping( Logger::INFO, Logger\Cli::SILENCE );
        $logger->setMapping( Logger::WARNING, Logger\Cli::STDOUT );
        $logger->setMapping( Logger::ERROR, Logger\Cli::STDOUT );

        $this->logSomething( $logger );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidMapping1()
    {
        $logger = new Cli();
        $logger->setMapping( 42, Logger\Cli::SILENCE );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidMapping2()
    {
        $logger = new Cli();
        $logger->setMapping( Logger::INFO, 42 );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidSeverity()
    {
        $logger = new Cli();
        $logger->log( 'Test', 42 );
    }
}
