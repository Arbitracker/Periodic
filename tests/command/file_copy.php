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

require_once 'tests/helper/logger.php';

class periodicCommandFileCopyTests extends periodicBaseTest
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function testEmptyConfiguation()
    {
        $cmd = new periodicFilesystemCopyCommand(
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
                '(E) No source provided.',
            ),
            $logger->logMessages
        );
    }

    public function testMissingDestination()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file</src>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::ERROR,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(E) No destination provided.',
            ),
            $logger->logMessages
        );
    }

    public function testCopyDirDefaultInfiniteDepth()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file/dir</src>
                    <dst>tests/tmp/test</dst>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileExists( $this->tmpDir . 'test/subdir/file1' );
    }

    public function testCopyDirDefaultLimitedDepth()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file/dir</src>
                    <dst>tests/tmp/test</dst>
                    <depth>2</depth>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileExists( $this->tmpDir . 'test/subdir' );
        $this->assertFileNotExists( $this->tmpDir . 'test/subdir/file1' );
    }

    public function testCopyFileDefaultInfiniteDepth()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file/file</src>
                    <dst>tests/tmp/test</dst>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileExists( $this->tmpDir . 'test' );
    }

    public function testCopyUnknwonFile()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file/not_existant</src>
                    <dst>tests/tmp/test</dst>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) tests/data/file/not_existant is not a valid source.',
            ),
            $logger->logMessages
        );
    }

    public function testCopyToExistingDirectory()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file/dir</src>
                    <dst>tests/tmp/existing</dst>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );
        mkdir( $this->tmpDir . '/existing' );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) tests/tmp/existing already exists, and cannot be overwritten.',
            ),
            $logger->logMessages
        );
    }

    public function testDirWithNonReadableDirectories()
    {
        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/data/file/dir</src>
                    <dst>tests/tmp/first</dst>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );
        $cmd->run();
        chmod( $this->tmpDir . '/first/second', 0 );

        $cmd = new periodicFilesystemCopyCommand(
            arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>tests/tmp/first</src>
                    <dst>tests/tmp/second</dst>
                </command>
            ' ),
            $logger = new periodicTestLogger()
        );

        $this->assertSame(
            periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) tests/tmp/first/second is not readable, skipping.',
            ),
            $logger->logMessages
        );

        $this->assertFileExists( $this->tmpDir . 'second/subdir' );
        $this->assertFileExists( $this->tmpDir . 'second/subdir/file1' );
        $this->assertFileNotExists( $this->tmpDir . 'second/second' );
    }
}
