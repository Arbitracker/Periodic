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
require_once 'tests/data/executor/public.php';

class periodicExecutorTests extends PHPUnit_Framework_TestCase
{
    protected $tmpDir;

    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/../tmp/';
    }

    public function tearDown()
    {
        foreach ( glob( $this->tmpDir . '*' ) as $file )
        {
            unlink( $file );
        }
    }

    public function testEmptyCronTable()
    {
        $executor = new periodicExecutor(
            "",
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
            "", $logger = new periodicTestLogger(), $this->tmpDir
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
            "", $logger = new periodicTestLogger(), $this->tmpDir
        );

        $executor->storeLastRun();
        $this->assertEquals(
            array( 'Stored last run time.' ),
            $logger->logMessages
        );

        $this->assertType( 'int', $executor->getLastRun() );
    }

    public function testLastRunDateStorageFailure()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $logger = new periodicTestLogger(), $this->tmpDir
        );

        $oldPerms = fileperms( $this->tmpDir );
        chmod( $this->tmpDir, 0 );

        $executor->storeLastRun();
        $this->assertSame(
            0,
            strpos( $logger->logMessages[0], 'Failure storing last run time' )
        );

        $this->assertSame( false, $executor->getLastRun() );
        chmod( $this->tmpDir, $oldPerms );
    }

    public function testAquireAndReleaseLock()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $logger = new periodicTestLogger(), $this->tmpDir
        );

        $this->assertTrue( $executor->aquireLock() );
        $executor->releaseLock();

        $this->assertEquals(
            array( 'Aquired lock.', 'Released lock.' ),
            $logger->logMessages
        );
    }

    public function testReAquireLock()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $logger = new periodicTestLogger(), $this->tmpDir
        );

        $this->assertTrue( $executor->aquireLock() );
        $this->assertFalse( $executor->aquireLock() );
        $executor->releaseLock();

        $this->assertEquals(
            array( 'Aquired lock.', 'Released lock.' ),
            $logger->logMessages
        );
    }

    public function testReleaseLockFailure()
    {
        $executor = new periodicTestAllPublicExecutor(
            "", $logger = new periodicTestLogger(), $this->tmpDir
        );

        $executor->releaseLock();
        $this->assertSame(
            0,
            strpos( $logger->logMessages[0], 'Failure releasing lock' )
        );
    }
}
