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

require_once 'test/Arbit/Periodic/helper/logger.php';
require_once 'test/Arbit/Periodic/helper/public.php';

class periodicExecutorTests extends periodicBaseTest
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function setUp()
    {
        parent::setUp();
        $this->commandFactory = new periodicCommandRegistry();
        $this->taskFactory = new periodicTaskFactory( dirname( __FILE__ ) . '/../_fixtures/tasks/', $this->commandFactory );
    }

    public function testEmptyCronTable()
    {
        $executor = new periodicExecutor(
            "",
            $this->taskFactory,
            $logger = new periodicTestLogger(),
            $this->tmpDir
        );

        $this->assertAttributeEquals(
            array(),
            'crontab', $executor
        );
    }

    public function testCrontableWithCommentsOnly()
    {
        $executor = new periodicTestAllPublicExecutor(
            "# Comment\n;Comment\r# Comment",
            $this->taskFactory,
            $logger = new periodicTestLogger(),
            $this->tmpDir
        );

        $this->assertAttributeEquals(
            array(),
            'crontab', $executor
        );
    }

    public function testValidCronLine()
    {
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * test",
            $this->taskFactory,
            $logger = new periodicTestLogger(),
            $this->tmpDir
        );

        $this->assertEquals(
            1,
            count( $executor->crontab )
        );
    }

    public function testMultipleCronLinesWithComments()
    {
        $executor = new periodicTestAllPublicExecutor(
            "\n# Line 1:\r\n* * * * * test\r; And a second one:\n* * * * * Foo\n",
            $this->taskFactory,
            $logger = new periodicTestLogger(),
            $this->tmpDir
        );

        $this->assertEquals(
            2,
            count( $executor->crontab )
        );
    }

    public function testGetInitialLastRunDate()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $this->assertSame( false, $executor->getLastRun() );

        $this->assertEquals(
            array(),
            $logger->logMessages
        );
    }

    public function testLastRunDateAfterStoreDate()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $executor->storeLastRun();
        $this->assertEquals(
            array( '(i) Stored last run time.' ),
            $logger->logMessages
        );

        $this->assertInternalType( 'int', $executor->getLastRun() );
    }

    public function testLastRunDateStorageFailure()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $oldPerms = fileperms( $this->tmpDir );
        chmod( $this->tmpDir, 0 );

        $executor->storeLastRun();
        $this->assertSame(
            0,
            strpos( $logger->logMessages[0], '(E) Failure storing last run time' )
        );

        $this->assertSame( false, $executor->getLastRun() );
        chmod( $this->tmpDir, $oldPerms );
    }

    public function testAquireAndReleaseLock()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $this->assertTrue( $executor->aquireLock() );
        $executor->releaseLock();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Released lock.'
            ),
            $logger->logMessages
        );
    }

    public function testReAquireLock()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $this->assertTrue( $executor->aquireLock() );
        $this->assertFalse( $executor->aquireLock() );
        $executor->releaseLock();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Released lock.'
            ),
            $logger->logMessages
        );
    }

    public function testReleaseLockFailure()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $executor->releaseLock();
        $this->assertSame(
            0,
            strpos( $logger->logMessages[0], '(E) Failure releasing lock' )
        );
    }

    public function testGetSingularJob()
    {
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * job1",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $jobs = $executor->getJobsSince( strtotime( "2000-01-01 12:23:34" ) );
        $this->assertEquals( 1, count( $jobs ) );
        $first = reset( $jobs );
        $this->assertEquals( "job1", $first[0]->task );
    }

    public function testGetMultipleJobs()
    {
        $executor = new periodicTestAllPublicExecutor(
            "*/15 * * * * *job2\n* * * * * job1",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $jobs = $executor->getJobsSince( strtotime( "2000-01-01 12:23:34" ) );
        $this->assertEquals( 2, count( $jobs ) );
        $first = reset( $jobs );
        $this->assertEquals( "job1", $first[0]->task );
    }

    public function testGetNoJobsInTimeframe()
    {
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * job1",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $jobs = $executor->getJobsSince( time() + 3600 );
        $this->assertEquals( 0, count( $jobs ) );
    }

    public function testDoNothingOnFirstRun()
    {
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * unknown",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        $executor->run();
        $this->assertFileExists( $this->tmpDir . '/lastRun' );
    }

    public function testUnknownTaskDefinitionFile()
    {
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * unknown",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Stored last run time.',
                '(E) Error reading definition file for task \'unknown\'',
                '(i) Released lock.',
            ),
            $logger->logMessages
        );
    }

    public function testInvalidTaskDefinitionFile()
    {
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * invalid",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();

        $this->assertSame(
            0,
            strpos( $logger->logMessages[2], '(E) Error parsing definition file for task \'invalid\':' )
        );
    }

    public function testRunDummyTestCommand()
    {
        $this->commandFactory->registerCommand( 'test.dummy', 'periodicTestDummyCommand' );
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * dummy",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Stored last run time.',
                '(i) Create task \'dummy\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                '(i) [dummy-1224] Start task execution.',
                '(i) [dummy-1224] Create command \'test.dummy\'.',
                '(i) [dummy-1224] Execute command \'test.dummy\'.',
                '(i) [dummy-1224] [test.dummy] Run test command.',
                '(i) [dummy-1224] Finished command execution.',
                '(i) [dummy-1224] Finished task execution.',
                '(i) Released lock.',
            ),
            $logger->logMessages
        );
    }

    public function testRunTwoCommandsWithSameCronEntry()
    {
        $this->commandFactory->registerCommand( 'test.dummy', 'periodicTestDummyCommand' );
        $executor = new periodicTestAllPublicExecutor(
            "* * * * * dummy\n* * * * * dummy",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );
        $executor->run();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Stored last run time.',
                '(i) Create task \'dummy\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                '(i) [dummy-1224] Start task execution.',
                '(i) [dummy-1224] Create command \'test.dummy\'.',
                '(i) [dummy-1224] Execute command \'test.dummy\'.',
                '(i) [dummy-1224] [test.dummy] Run test command.',
                '(i) [dummy-1224] Finished command execution.',
                '(i) [dummy-1224] Finished task execution.',
                '(i) Create task \'dummy\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                '(i) [dummy-1224] Start task execution.',
                '(i) [dummy-1224] Create command \'test.dummy\'.',
                '(i) [dummy-1224] Execute command \'test.dummy\'.',
                '(i) [dummy-1224] [test.dummy] Run test command.',
                '(i) [dummy-1224] Finished command execution.',
                '(i) [dummy-1224] Finished task execution.',
                '(i) Released lock.',
            ),
            $logger->logMessages
        );
    }

    /**
     * This test will fail when run between 0:00.0 and 0:00.30 on new years eve.
     */
    public function testRescheduleTask()
    {
        $this->commandFactory->registerCommand( 'test.dummy', 'periodicTestDummyCommand' );
        $this->commandFactory->registerCommand( 'test.reschedule', 'periodicTestRescheduleCommand' );

        $executor = new periodicTestAllPublicExecutor(
            "0 0 1 1 * reschedule",
            $this->taskFactory, $logger = new periodicTestLogger(), $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );

        // First run, should reschedule the test
        $executor->run();

        // Second run - should run rescheduled test only.
        $executor->run();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Stored last run time.',
                '(i) Create task \'reschedule\' for scheduled date \'Mon, 01 Jan 2001 00:00:00 +0100\'.',
                '(i) [reschedule-0000] Start task execution.',
                '(i) [reschedule-0000] Create command \'test.dummy\'.',
                '(i) [reschedule-0000] Execute command \'test.dummy\'.',
                '(i) [reschedule-0000] [test.dummy] Run test command.',
                '(i) [reschedule-0000] Finished command execution.',
                '(i) [reschedule-0000] Create command \'test.reschedule\'.',
                '(i) [reschedule-0000] Execute command \'test.reschedule\'.',
                '(W) [reschedule-0000] [test.reschedule] Run test reschedule command.',
                '(i) [reschedule-0000] Command requested rescheduled execution.',
                '(i) [reschedule-0000] Task will be rescheduled for 30 seconds.',
                '(i) Released lock.',
                '(i) Aquired lock.',
                '(i) Stored last run time.',
                '(i) Create task \'reschedule\' for scheduled date \'Mon, 01 Jan 2001 00:00:30 +0100\'.',
                '(i) [reschedule-0000] Start task execution.',
                '(i) [reschedule-0000] Create command \'test.dummy\'.',
                '(i) [reschedule-0000] Execute command \'test.dummy\'.',
                '(i) [reschedule-0000] [test.dummy] Run test command.',
                '(i) [reschedule-0000] Finished command execution.',
                '(i) [reschedule-0000] Create command \'test.reschedule\'.',
                '(i) [reschedule-0000] Execute command \'test.reschedule\'.',
                '(W) [reschedule-0000] [test.reschedule] Run test reschedule command.',
                '(i) [reschedule-0000] Command requested rescheduled execution.',
                '(i) [reschedule-0000] Task will be rescheduled for 30 seconds.',
                '(i) Released lock.',
            ),
            $logger->logMessages
        );
    }
}
