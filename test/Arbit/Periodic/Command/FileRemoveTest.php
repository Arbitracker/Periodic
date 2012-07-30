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

namespace Arbit\Periodic\Command;

use Arbit\Periodic\TestCase;

require_once __DIR__ . '/../TestCase.php';

require_once 'test/Arbit/Periodic/helper/Logger.php';

class FileRemoveTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $cmd = new \periodicFilesystemCopyCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>test/Arbit/Periodic/_fixtures/file/dir</src>
                    <dst>test/Arbit/Periodic/tmp/dir</dst>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );
        $cmd->run();
    }

    public function testEmptyConfiguation()
    {
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command/>
            ' ),
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            \periodicExecutor::ERROR,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(E) No path provided.',
            ),
            $logger->logMessages
        );
    }

    public function testRemoveNotExistingDirectory()
    {
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/not_existing</path>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) test/Arbit/Periodic/not_existing is not a valid source.',
            ),
            $logger->logMessages
        );
    }

    public function testRemoveNotReadableFile()
    {
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/tmp/dir/subdir/file1</path>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );
        chmod( $this->tmpDir . 'dir/subdir/file1', 0 );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) test/Arbit/Periodic/tmp/dir/subdir/file1 is not readable, skipping.',
            ),
            $logger->logMessages
        );
    }

    public function testRemoveInNotWriteableParentDir()
    {
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/tmp/dir/subdir/file1</path>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );
        chmod( $this->tmpDir . 'dir/subdir', 0555 );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertEquals(
            array(
                '(W) test/Arbit/Periodic/tmp/dir/subdir is not writable, skipping.',
            ),
            $logger->logMessages
        );
    }

    public function testRemoveDirDefaultInfinitePattern()
    {
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/tmp/dir</path>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileNotExists( $this->tmpDir . 'dir' );
    }

    public function testRemoveDirSimpleFilePattern()
    {
        $this->assertFileExists( $this->tmpDir . 'dir' );
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/tmp/dir</path>
                    <pattern>file*</pattern>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileExists( $this->tmpDir . 'dir' );
        $this->assertFileExists( $this->tmpDir . 'dir/subdir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file1' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file2' );
    }

    public function testRemoveDirSimpleDirPattern()
    {
        $this->assertFileExists( $this->tmpDir . 'dir' );
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/tmp/dir</path>
                    <pattern>subdir</pattern>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileExists( $this->tmpDir . 'dir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file1' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file2' );
    }

    public function testRemoveFile()
    {
        $this->assertFileExists( $this->tmpDir . 'dir' );
        $cmd = new \periodicFilesystemRemoveCommand(
            \arbitXml::loadString( '<?xml version="1.0" ?>
                <command>
                    <path>test/Arbit/Periodic/tmp/dir/subdir/file1</path>
                </command>
            ' ),
            $logger = new \periodicTestLogger()
        );

        $this->assertSame(
            \periodicExecutor::SUCCESS,
            $cmd->run()
        );

        $this->assertFileExists( $this->tmpDir . 'dir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file1' );
    }
}
