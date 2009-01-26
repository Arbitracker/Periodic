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

/**
 * Periodic base test
 */
abstract class periodicBaseTest extends PHPUnit_Framework_TestCase
{
    protected $tmpDir = null;

    public function setUp()
    {
        $this->tmpDir = __DIR__ . '/tmp/';
        
        // Ensure tmpdir has proper access right
        chmod( $this->tmpDir, 0755 );
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
                $this->removeRecursively( $dir );
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
}
