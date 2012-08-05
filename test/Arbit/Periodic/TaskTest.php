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

namespace Arbit\Periodic;

use Arbit\Xml;

require_once __DIR__ . '/TestCase.php';

class TaskTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->commandRegistry = new CommandRegistry();
        $this->commandRegistry->registerCommand( 'test.dummy', $this->getSuccessfulCommand() );
        $this->commandRegistry->registerCommand( 'test.abort', $this->getAbortCommand() );
        $this->commandRegistry->registerCommand( 'test.reschedule', $this->getRescheduleCommand() );
        $this->commandRegistry->registerCommand( 'test.error', $this->getErrorCommand() );
        $this->commandRegistry->registerCommand( 'test.errorneous', $this->getErrornousCommand() );
        $this->commandRegistry->registerCommand( 'test.exception', $this->getCommandThrowingException() );
    }

    public function testTaskConfigurationDefaultValues()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/_fixtures/tasks/dummy.xml" ),
            $this->commandRegistry,
            $this->getLogger()
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
            Xml\Document::loadFile( __DIR__ . "/_fixtures/tasks/dummy.xml" ),
            $this->commandRegistry,
            $this->getLogger()
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
            Xml\Document::loadFile( __DIR__ . "/_fixtures/tasks/dummy.xml" ),
            $this->commandRegistry,
            $this->getLogger()
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
            Xml\Document::loadFile( __DIR__ . "/_fixtures/tasks/dummy.xml" ),
            $this->commandRegistry,
            $this->getLogger()
        );

        $task->timeout = 42;
    }

    public function testTaskConfigurationReconfiguredValues()
    {
        $task = new Task(
            'test', 0,
            Xml\Document::loadFile( __DIR__ . "/_fixtures/tasks/reschedule.xml" ),
            $this->commandRegistry,
            $this->getLogger()
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
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                ),
            ),
            array(
                'multiple',
                Executor::SUCCESS,
                array(
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                ),
            ),
            array(
                'abort',
                Executor::SUCCESS,
                array(
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.abort\'.',
                    'Command aborted execution.',
                ),
            ),
            array(
                'reschedule',
                Executor::RESCHEDULE,
                array(
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.reschedule\'.',
                    'Command requested rescheduled execution.',
                ),
            ),
            array(
                'error',
                Executor::ERROR,
                array(
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.error\'.',
                    'Command reported error.',
                ),
            ),
            array(
                'errorneous',
                Executor::ERROR,
                array(
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.errorneous\'.',
                    'Command returned in unknown state.',
                ),
            ),
            array(
                'exception',
                Executor::ERROR,
                array(
                    'Execute command \'test.dummy\'.',
                    'Finished command execution.',
                    'Execute command \'test.exception\'.',
                    'Command threw exception: Hello world!',
                    'Command returned in unknown state.',
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
            Xml\Document::loadFile( __DIR__ . "/_fixtures/tasks/$name.xml" ),
            $this->commandRegistry,
            $this->getLogger( $log )
        );

        $this->assertSame(
            $status,
            $task->execute()
        );
    }
}
