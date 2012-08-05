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

require_once __DIR__ . '/TestCase.php';

require_once 'test/Arbit/Periodic/helper/Public.php';

class ExecutorTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->commandFactory = new CommandRegistry();
        $this->taskFactory = new TaskFactory( __DIR__ . '/_fixtures/tasks/', $this->commandFactory );
    }

    public function testEmptyCronTable()
    {
        $executor = new Executor(
            "",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $this->assertAttributeEquals(
            array(),
            'crontab', $executor
        );
    }

    public function testCrontableWithCommentsOnly()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "# Comment\n;Comment\r# Comment",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $this->assertAttributeEquals(
            array(),
            'crontab', $executor
        );
    }

    public function testValidCronLine()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * test",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $this->assertEquals(
            1,
            count( $executor->crontab )
        );
    }

    public function testMultipleCronLinesWithComments()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "\n# Line 1:\r\n* * * * * test\r; And a second one:\n* * * * * Foo\n",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $this->assertEquals(
            2,
            count( $executor->crontab )
        );
    }

    public function testGetInitialLastRunDate()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "",
            $this->taskFactory,
            $this->getLogger( array() ),
            $this->tmpDir
        );

        $this->assertSame( false, $executor->getLastRun() );
    }

    public function testLastRunDateAfterStoreDate()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "",
            $this->taskFactory,
            $this->getLogger( array(
                'Stored last run time.'
            ) ),
            $this->tmpDir
        );

        $executor->storeLastRun();
        $this->assertInternalType( 'int', $executor->getLastRun() );
    }

    public function testLastRunDateStorageFailure()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "",
            $this->taskFactory,
            $this->getLogger( array(
                'Failure storing last run time'
            ) ),
            $this->tmpDir
        );

        $oldPerms = fileperms( $this->tmpDir );
        chmod( $this->tmpDir, 0 );

        $executor->storeLastRun();
        $this->assertSame( false, $executor->getLastRun() );
        chmod( $this->tmpDir, $oldPerms );
    }

    public function testAquireAndReleaseLock()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        $this->assertTrue( $executor->aquireLock() );
        $executor->releaseLock();
    }

    public function testReAquireLock()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'The lockfile ' . $this->tmpDir . '/lock does already exist.',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        $this->assertTrue( $executor->aquireLock() );
        $this->assertFalse( $executor->aquireLock() );
        $executor->releaseLock();
    }

    public function testReleaseLockFailure()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "",
            $this->taskFactory,
            $this->getLogger( array(
                'Failure releasing lock',
            ) ),
            $this->tmpDir
        );

        $executor->releaseLock();
    }

    public function testGetSingularJob()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * job1",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $jobs = $executor->getJobsSince( strtotime( "2000-01-01 12:23:34" ) );
        $this->assertEquals( 1, count( $jobs ) );
        $first = reset( $jobs );
        $this->assertEquals( "job1", $first[0]->task );
    }

    public function testGetMultipleJobs()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "*/15 * * * * *job2\n* * * * * job1",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $jobs = $executor->getJobsSince( strtotime( "2000-01-01 12:23:34" ) );
        $this->assertEquals( 2, count( $jobs ) );
        $first = reset( $jobs );
        $this->assertEquals( "job1", $first[0]->task );
    }

    public function testGetNoJobsInTimeframe()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * job1",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $jobs = $executor->getJobsSince( time() + 3600 );
        $this->assertEquals( 0, count( $jobs ) );
    }

    public function testDoNothingOnFirstRun()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * unknown",
            $this->taskFactory,
            $this->getLogger(),
            $this->tmpDir
        );

        $executor->run();
        $this->assertFileExists( $this->tmpDir . '/lastRun' );
    }

    public function testUnknownTaskDefinitionFile()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * unknown",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Stored last run time.',
                'Error reading definition file for task \'unknown\'',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();
    }

    public function testInvalidTaskDefinitionFile()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * invalid",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Stored last run time.',
                'Error parsing definition file for task \'invalid\'',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();
    }

    public function testRunDummyTestCommand()
    {
        $this->commandFactory->registerCommand( 'test.dummy', $this->getSuccessfulCommand() );
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * dummy",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Stored last run time.',
                'Create task \'dummy\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                'Start task execution.',
                'Execute command \'test.dummy\'.',
                'Finished command execution.',
                'Finished task execution.',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();
    }

    public function testRunTwoCommandsWithSameCronEntry()
    {
        $this->commandFactory->registerCommand( 'test.dummy', $this->getSuccessfulCommand() );
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * dummy\n* * * * * dummy",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Stored last run time.',
                'Create task \'dummy\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                'Start task execution.',
                'Execute command \'test.dummy\'.',
                'Finished command execution.',
                'Finished task execution.',
                'Create task \'dummy\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                'Start task execution.',
                'Execute command \'test.dummy\'.',
                'Finished command execution.',
                'Finished task execution.',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();
    }

    /**
     * This test will fail when run between 0:00.0 and 0:00.30 on new years eve.
     */
    public function testRescheduleTask()
    {
        $this->commandFactory->registerCommand( 'test.dummy', $this->getSuccessfulCommand() );
        $this->commandFactory->registerCommand( 'test.reschedule', $this->getRescheduleCommand() );

        $executor = new \periodicTestAllPublicExecutor(
            "0 0 1 1 * reschedule",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Stored last run time.',
                'Create task \'reschedule\' for scheduled date \'Mon, 01 Jan 2001 00:00:00 +0100\'.',
                'Start task execution.',
                'Execute command \'test.dummy\'.',
                'Finished command execution.',
                'Execute command \'test.reschedule\'.',
                'Command requested rescheduled execution.',
                'Task will be rescheduled for 30 seconds.',
                'Released lock.',
                'Aquired lock.',
                'Stored last run time.',
                'Create task \'reschedule\' for scheduled date \'Mon, 01 Jan 2001 00:00:30 +0100\'.',
                'Start task execution.',
                'Execute command \'test.dummy\'.',
                'Finished command execution.',
                'Execute command \'test.reschedule\'.',
                'Command requested rescheduled execution.',
                'Task will be rescheduled for 30 seconds.',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );

        // First run, should reschedule the test
        $executor->run();

        // Second run - should run rescheduled test only.
        $executor->run();
    }

    public function testFullExecutorRun()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * functional",
            $this->taskFactory,
            $this->getLogger( array(
                'Aquired lock.',
                'Stored last run time.',
                'Create task \'functional\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                'Start task execution.',
                'Execute command \'fs.copy\'.',
                'Finished command execution.',
                'Execute command \'fs.remove\'.',
                'Finished command execution.',
                'Execute command \'system.exec\'.',
                'Hello world',
                'Command exited with return value 0',
                'Finished command execution.',
                'Finished task execution.',
                'Released lock.',
            ) ),
            $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );

        // First run, should reschedule the test
        $executor->run();

        // Second run - should run rescheduled test only.
        $executor->run();
    }
}
