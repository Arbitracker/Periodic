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

require_once __DIR__ . '/../TestCase.php';

require_once 'test/Arbit/Periodic/helper/logger.php';
require_once 'test/Arbit/Periodic/helper/cli_logger.php';

class periodicLoggerCliTests extends TestCase
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function setUp()
    {
        parent::setUp();
    }

    protected function logSomething( periodicLogger $logger )
    {
        $logger->log( 'Info 1' );
        $logger->setTask( 'task1' );
        $logger->log( 'Info 2' );
        $logger->setCommand( 'command1' );
        $logger->log( 'Info 3' );
        $logger->setTask();
        $logger->log( 'Warning', periodicLogger::WARNING );
        $logger->log( 'Error', periodicLogger::ERROR );
    }

    public function testDefaultLogging()
    {
        $logger = new periodicTestCliLogger();

        $this->logSomething( $logger );

        $this->assertSame(
            array(
                "php://stdout" => array(
                    "[date] Info: Info 1\n",
                    "[date] (task1) Info: Info 2\n",
                    "[date] (task1::command1) Info: Info 3\n",
                ),
                "php://stderr" => array(
                    "[date] Warning: Warning\n",
                    "[date] Error: Error\n",
                ),
            ),
            $logger->texts
        );
    }

    public function testRemappedLogging()
    {
        $logger = new periodicTestCliLogger();
        $logger->setMapping( periodicLogger::INFO, periodicCliLogger::SILENCE );
        $logger->setMapping( periodicLogger::WARNING, periodicCliLogger::STDOUT );
        $logger->setMapping( periodicLogger::ERROR, periodicCliLogger::STDOUT );

        $this->logSomething( $logger );

        $this->assertSame(
            array(
                "php://stdout" => array(
                    "[date] Warning: Warning\n",
                    "[date] Error: Error\n",
                ),
                "php://stderr" => array(
                ),
            ),
            $logger->texts
        );
    }

    public function testInvalidMapping1()
    {
        $logger = new periodicTestCliLogger();

        try
        {
            $logger->setMapping( 42, periodicCliLogger::SILENCE );
            $this->fail( 'Expected periodicRuntimeException.' );
        }
        catch ( periodicRuntimeException $e )
        { /* Expected */ }
    }

    public function testInvalidMapping2()
    {
        $logger = new periodicTestCliLogger();

        try
        {
            $logger->setMapping( periodicLogger::INFO, 42 );
            $this->fail( 'Expected periodicRuntimeException.' );
        }
        catch ( periodicRuntimeException $e )
        { /* Expected */ }
    }

    public function testInvalidSeverity()
    {
        $logger = new periodicTestCliLogger();

        try
        {
            $logger->log( 'Test', 42 );
            $this->fail( 'Expected periodicRuntimeException.' );
        }
        catch ( periodicRuntimeException $e )
        { /* Expected */ }
    }
}
