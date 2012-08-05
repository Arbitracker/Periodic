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

namespace Arbit\Periodic;

// Workaround around PHPUnits incapability to source files, used in
// mocks, and thus creating wrong mocks
new \Arbit\Xml\Document();

/**
 * Periodic base test
 */
abstract class TestCase extends \PHPUnit_Framework_TestCase
{
    protected $tmpDir = null;

    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/tmp/';

        // Since git does not allow to check in empty directories, we create
        // one, if it does not exit
        if ( !is_dir( $dir = __DIR__ . '/_fixtures/file/dir/second' ) )
        {
            mkdir( $dir );
        }

        // Ensure tmpdir has proper access right
        chmod( $this->tmpDir, 0755 );

        // Change the current working dir, so the test suite is not required to 
        // be executed from a certain base dir
        chdir( __DIR__ . '/../../../' );
    }

    public function tearDown()
    {
        if ( $this->tmpDir === null )
        {
            // Test case tem dir has not been initilized, no need to clean up.
            return;
        }

        // Ensure tmpdir has proper access right
        chmod( $this->tmpDir, 0755 );
        foreach ( glob( $this->tmpDir . '*' ) as $file )
        {
            if ( is_dir( $file ) )
            {
                $this->removeRecursively( $file );
            }
            else
            {
                chmod( $file, 0700 );
                unlink( $file );
            }
        }
    }

    /**
     * Remove directory
     *
     * Delete the given directory and all of its contents recusively.
     *
     * @param string $dir
     * @return void
     */
    protected function removeRecursively( $dir )
    {
        chmod( $dir, 0700 );
        $directory = dir( $dir );
        while ( ( $path = $directory->read() ) !== false )
        {
            if ( ( $path === '.' ) ||
                 ( $path === '..' ) )
            {
                continue;
            }
            $path = $dir . '/' . $path;

            if ( is_dir( $path ) )
            {
                $this->removeRecursively( $path );
            }
            else
            {
                chmod( $path, 0700 );
                unlink( $path );
            }
        }

        rmdir( $dir );
    }

    protected function getSuccessfulCommand()
    {
        $command = $this->getMock( '\\Arbit\\Periodic\\Command' );
        $command
            ->expects( $this->any() )
            ->method( 'run' )
            ->will( $this->returnValue( Executor::SUCCESS ) );

        return $command;
    }

    protected function getRescheduleCommand()
    {
        $command = $this->getMock( '\\Arbit\\Periodic\\Command' );
        $command
            ->expects( $this->any() )
            ->method( 'run' )
            ->will( $this->returnValue( Executor::RESCHEDULE ) );

        return $command;
    }

    protected function getAbortCommand()
    {
        $command = $this->getMock( '\\Arbit\\Periodic\\Command' );
        $command
            ->expects( $this->any() )
            ->method( 'run' )
            ->will( $this->returnValue( Executor::ABORT ) );

        return $command;
    }

    protected function getErrorCommand()
    {
        $command = $this->getMock( '\\Arbit\\Periodic\\Command' );
        $command
            ->expects( $this->any() )
            ->method( 'run' )
            ->will( $this->returnValue( Executor::ERROR ) );

        return $command;
    }

    protected function getErrornousCommand()
    {
        $command = $this->getMock( '\\Arbit\\Periodic\\Command' );
        $command
            ->expects( $this->any() )
            ->method( 'run' )
            ->will( $this->returnValue( null ) );

        return $command;
    }

    protected function getCommandThrowingException()
    {
        $command = $this->getMock( '\\Arbit\\Periodic\\Command' );
        $command
            ->expects( $this->any() )
            ->method( 'run' )
            ->will( $this->throwException( new \RuntimeException( "Hello world!" ) ) );

        return $command;
    }
}
