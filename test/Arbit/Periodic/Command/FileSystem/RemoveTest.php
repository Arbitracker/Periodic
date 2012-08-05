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

namespace Arbit\Periodic\Command\FileSystem;

use Arbit\Periodic\TestCase,
    Arbit\Periodic\Executor,
    Arbit\Xml;

require_once __DIR__ . '/../../TestCase.php';

class RemoveTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $cmd = new Copy();
        $cmd->run(
            Xml\Document::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>test/Arbit/Periodic/_fixtures/file/dir</src>
                    <dst>test/Arbit/Periodic/tmp/dir</dst>
                </command>
            ' ),
            $this->getLogger()
        );
    }

    public function testEmptyConfiguation()
    {
        $cmd = new Remove();

        $this->assertSame(
            Executor::ERROR,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command/>
                ' ),
                $this->getLogger( array(
                    'No path provided.',
                ) )
            )
        );
    }

    public function testRemoveNotExistingDirectory()
    {
        $cmd = new Remove();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/not_existing</path>
                    </command>
                ' ),
                $this->getLogger( array(
                    'test/Arbit/Periodic/not_existing is not a valid source.',
                ) )
            )
        );
    }

    public function testRemoveNotReadableFile()
    {
        $cmd = new Remove();
        chmod( $this->tmpDir . 'dir/subdir/file1', 0 );

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/tmp/dir/subdir/file1</path>
                    </command>
                ' ),
                $this->getLogger( array(
                    'test/Arbit/Periodic/tmp/dir/subdir/file1 is not readable, skipping.',
                ) )
            )
        );
    }

    public function testRemoveInNotWriteableParentDir()
    {
        $cmd = new Remove();
        chmod( $this->tmpDir . 'dir/subdir', 0555 );

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/tmp/dir/subdir/file1</path>
                    </command>
                ' ),
                $this->getLogger( array(
                    'test/Arbit/Periodic/tmp/dir/subdir is not writable, skipping.',
                ) )
            )
        );
    }

    public function testRemoveDirDefaultInfinitePattern()
    {
        $cmd = new Remove();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/tmp/dir</path>
                    </command>
                ' ),
                $this->getLogger( array(
                ) )
            )
        );

        $this->assertFileNotExists( $this->tmpDir . 'dir' );
    }

    public function testRemoveDirSimpleFilePattern()
    {
        $this->assertFileExists( $this->tmpDir . 'dir' );
        $cmd = new Remove();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/tmp/dir</path>
                        <pattern>file*</pattern>
                    </command>
                ' ),
                $this->getLogger( array(
                ) )
            )
        );

        $this->assertFileExists( $this->tmpDir . 'dir' );
        $this->assertFileExists( $this->tmpDir . 'dir/subdir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file1' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file2' );
    }

    public function testRemoveDirSimpleDirPattern()
    {
        $this->assertFileExists( $this->tmpDir . 'dir' );
        $cmd = new Remove();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/tmp/dir</path>
                        <pattern>subdir</pattern>
                    </command>
                ' ),
                $this->getLogger( array(
                ) )
            )
        );

        $this->assertFileExists( $this->tmpDir . 'dir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file1' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file2' );
    }

    public function testRemoveFile()
    {
        $this->assertFileExists( $this->tmpDir . 'dir' );
        $cmd = new Remove();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <path>test/Arbit/Periodic/tmp/dir/subdir/file1</path>
                    </command>
                ' ),
                $this->getLogger( array(
                ) )
            )
        );

        $this->assertFileExists( $this->tmpDir . 'dir' );
        $this->assertFileNotExists( $this->tmpDir . 'dir/subdir/file1' );
    }
}
