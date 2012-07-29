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

require_once 'test/helper/logger.php';

class periodicCommandSystemExecTests extends periodicBaseTest
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function testEmptyConfiguation()
    {
        $cmd = new periodicSystemExecCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command/>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::ERROR,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(E) No command provided for execution.',
            ),
            $logger->logMessages
        );
    }

    public function testSuccessfullCommandExecution()
    {
        $cmd = new periodicSystemExecCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>echo "Hello world"</command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(i) Hello world',
                '(i) Command exited with return value 0',
            ),
            $logger->logMessages
        );
    }

    public function testFailOnUnknownCommand()
    {
        $cmd = new periodicSystemExecCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>some_command_not_available</command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::ERROR,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) sh: some_command_not_available: command not found',
                '(i) Command exited with return value 127',
            ),
            $logger->logMessages
        );
    }

    public function testNoFailOnUnknownCommand()
    {
        $cmd = new periodicSystemExecCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command failOnError="false">some_command_not_available</command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) sh: some_command_not_available: command not found',
                '(i) Command exited with return value 127',
            ),
            $logger->logMessages
        );
    }
}
