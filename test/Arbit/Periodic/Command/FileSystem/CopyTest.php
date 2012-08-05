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

class CopyTest extends TestCase
{
    public function testEmptyConfiguration()
    {
        $cmd = new Copy();

        $this->assertSame(
            Executor::ERROR,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command/>
                ' ),
                $this->getLogger( array(
                    'No source provided.',
                ) )
            )
        );
    }

    public function testMissingDestination()
    {
        $cmd = new Copy();

        $this->assertSame(
            Executor::ERROR,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/_fixtures/file</src>
                    </command>
                ' ),
                $this->getLogger( array(
                    'No destination provided.',
                ) )
            )
        );
    }

    public function testCopyDirDefaultInfiniteDepth()
    {
        $cmd = new Copy();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/_fixtures/file/dir</src>
                        <dst>test/Arbit/Periodic/tmp/test</dst>
                    </command>
                ' ),
                $this->getLogger()
            )
        );

        $this->assertFileExists( $this->tmpDir . 'test/subdir/file1' );
    }

    public function testCopyDirDefaultLimitedDepth()
    {
        $cmd = new Copy();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/_fixtures/file/dir</src>
                        <dst>test/Arbit/Periodic/tmp/test</dst>
                        <depth>2</depth>
                    </command>
                ' ),
                $this->getLogger()
            )
        );

        $this->assertFileExists( $this->tmpDir . 'test/subdir' );
        $this->assertFileNotExists( $this->tmpDir . 'test/subdir/file1' );
    }

    public function testCopyFileDefaultInfiniteDepth()
    {
        $cmd = new Copy();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/_fixtures/file/file</src>
                        <dst>test/Arbit/Periodic/tmp/test</dst>
                    </command>
                ' ),
                $this->getLogger()
            )
        );

        $this->assertFileExists( $this->tmpDir . 'test' );
    }

    public function testCopyUnknownFile()
    {
        $cmd = new Copy();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/_fixtures/file/not_existant</src>
                        <dst>test/Arbit/Periodic/tmp/test</dst>
                    </command>
                ' ),
                $this->getLogger( array(
                    'test/Arbit/Periodic/_fixtures/file/not_existant is not a valid source.',
                ) )
            )
        );
    }

    public function testCopyToExistingDirectory()
    {
        $cmd = new Copy();
        mkdir( $this->tmpDir . '/existing' );

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/_fixtures/file/dir</src>
                        <dst>test/Arbit/Periodic/tmp/existing</dst>
                    </command>
                ' ),
                $this->getLogger( array(
                    'test/Arbit/Periodic/tmp/existing already exists, and cannot be overwritten.',
                ) )
            )
        );
    }

    public function testDirWithNonReadableDirectories()
    {
        $cmd = new Copy();
        $cmd->run(
            Xml\Document::loadString( '<?xml version="1.0" ?>
                <command>
                    <src>test/Arbit/Periodic/_fixtures/file/dir</src>
                    <dst>test/Arbit/Periodic/tmp/first</dst>
                </command>
            ' ),
            $this->getLogger()
        );
        chmod( $this->tmpDir . '/first/second', 0 );

        $cmd = new Copy();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>
                        <src>test/Arbit/Periodic/tmp/first</src>
                        <dst>test/Arbit/Periodic/tmp/second</dst>
                    </command>
                ' ),
                $this->getLogger( array(
                    'test/Arbit/Periodic/tmp/first/second is not readable, skipping.',
                ) )
            )
        );

        $this->assertFileExists( $this->tmpDir . 'second/subdir' );
        $this->assertFileExists( $this->tmpDir . 'second/subdir/file1' );
        $this->assertFileNotExists( $this->tmpDir . 'second/second' );
    }
}
