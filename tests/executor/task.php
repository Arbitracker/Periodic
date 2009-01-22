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
 * @subpackage Executor
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

require_once 'tests/data/logger.php';
require_once 'tests/data/command.php';

class periodicTaskTests extends PHPUnit_Framework_TestCase
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function setUp()
    {
        periodicCommandRegistry::registerCommand( 'test.dummy', 'periodicTestDummyCommand' );
        periodicCommandRegistry::registerCommand( 'test.abort', 'periodicTestAbortCommand' );
        periodicCommandRegistry::registerCommand( 'test.reschedule', 'periodicTestRescheduleCommand' );
        periodicCommandRegistry::registerCommand( 'test.error', 'periodicTestErrorCommand' );
    }

    public static function getTaskHandlingLogs()
    {
        return array(
            array(
                'dummy',
                periodicExecutor::SUCCESS,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                ),
            ),
            array(
                'multiple',
                periodicExecutor::SUCCESS,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                ),
            ),
            array(
                'abort',
                periodicExecutor::SUCCESS,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                    '(i) Create command \'test.abort\'.',
                    '(i) Execute command \'test.abort\'.',
                    '(W) [test.abort] Run test abortion command.',
                    '(i) Command aborted execution.',
                ),
            ),
            array(
                'reschedule',
                periodicExecutor::RESCHEDULE,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                    '(i) Create command \'test.reschedule\'.',
                    '(i) Execute command \'test.reschedule\'.',
                    '(W) [test.reschedule] Run test reschedule command.',
                    '(i) Command requested rescheduled execution.',
                ),
            ),
            array(
                'error',
                periodicExecutor::ERROR,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                    '(i) Create command \'test.error\'.',
                    '(i) Execute command \'test.error\'.',
                    '(E) [test.error] Run test error command.',
                    '(W) Command reported error.',
                ),
            ),
        );
    }

    /**
     * @dataProvider getTaskHandlingLogs
     */
    public function testRunDummyTestCommand( $name, $status, $log )
    {
        $task = new periodicTask(
            'test', 0,
            arbitXml::loadFile( __DIR__ . "/../data/tasks/$name.xml" ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            $status,
            $task->execute()
        );

        $this->assertEquals(
            $log,
            $logger->logMessages
        );
    }
}
