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

namespace Arbit\Periodic\Executor;

use Arbit\Periodic\TestCase,
    Arbit\Xml,
    Arbit\Periodic\Task,
    Arbit\Periodic\CommandRegistry,
    Arbit\Periodic\Executor;

require_once __DIR__ . '/../TestCase.php';

require_once 'test/Arbit/Periodic/helper/Logger.php';
require_once 'test/Arbit/Periodic/helper/Command.php';

class TaskTest extends TestCase
{
    public function setUp()
    {
        $this->commandFactory = new CommandRegistry();
        $this->commandFactory->registerCommand( 'test.dummy', '\periodicTestDummyCommand' );
        $this->commandFactory->registerCommand( 'test.abort', '\periodicTestAbortCommand' );
        $this->commandFactory->registerCommand( 'test.reschedule', '\periodicTestRescheduleCommand' );
        $this->commandFactory->registerCommand( 'test.error', '\periodicTestErrorCommand' );
        $this->commandFactory->registerCommand( 'test.errorneous', '\periodicTestErrorneousCommand' );
    }

    public function testTaskConfigurationDefaultValues()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/../_fixtures/tasks/dummy.xml" ),
            $this->commandFactory,
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            300,
            $task->reScheduleTime
        );

        $this->assertSame(
            3600,
            $task->timeout
        );
    }

    /**
     * @expectedException \Arbit\Periodic\AttributeException
     */
    public function testTaskConfigurationReadUnknownValue()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/../_fixtures/tasks/dummy.xml" ),
            $this->commandFactory,
            $logger = new \periodicTestLogger()
        );

        $task->unknown;
    }

    /**
     * @expectedException \Arbit\Periodic\AttributeException
     */
    public function testTaskConfigurationWriteUnknownValue()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/../_fixtures/tasks/dummy.xml" ),
            $this->commandFactory,
            $logger = new \periodicTestLogger()
        );

        $task->unknown = 42;
    }

    /**
     * @expectedException \Arbit\Periodic\AttributeException
     */
    public function testTaskConfigurationWriteValue()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/../_fixtures/tasks/dummy.xml" ),
            $this->commandFactory,
            $logger = new \periodicTestLogger()
        );

        $task->timeout = 42;
    }

    public function testTaskConfigurationReconfiguredValues()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/../_fixtures/tasks/reschedule.xml" ),
            $this->commandFactory,
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            30,
            $task->reScheduleTime
        );

        $this->assertSame(
            1200,
            $task->timeout
        );
    }

    public static function getTaskHandlingLogs()
    {
        return array(
            array(
                'dummy',
                Executor::SUCCESS,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                ),
            ),
            array(
                'multiple',
                Executor::SUCCESS,
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
                Executor::SUCCESS,
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
                Executor::RESCHEDULE,
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
                Executor::ERROR,
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
            array(
                'errorneous',
                Executor::ERROR,
                array(
                    '(i) Create command \'test.dummy\'.',
                    '(i) Execute command \'test.dummy\'.',
                    '(i) [test.dummy] Run test command.',
                    '(i) Finished command execution.',
                    '(i) Create command \'test.errorneous\'.',
                    '(i) Execute command \'test.errorneous\'.',
                    '(i) [test.errorneous] Run command returnin nothing.',
                    '(E) Command returned in unknown state.',
                ),
            ),
        );
    }

    /**
     * @dataProvider getTaskHandlingLogs
     */
    public function testRunDummyTestCommand( $name, $status, $log )
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/../_fixtures/tasks/$name.xml" ),
            $this->commandFactory,
            $logger = new \periodicTestLogger()
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
