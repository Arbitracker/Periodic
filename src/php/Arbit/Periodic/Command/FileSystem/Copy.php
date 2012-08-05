<?php
/**
 * Command
 *
 * This file is part of periodic
 *
 * periodic is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * periodic is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with periodic; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision: 999 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

namespace Arbit\Periodic\Command\FileSystem;

use Arbit\Periodic\Command,
    Arbit\Periodic\Executor,
    Arbit\Periodic\Logger,
    Arbit\XML;

/**
 * Command
 *
 * Command to (recursively) copy files.
 */
class Copy extends Command
{
    /**
     * Run command
     *
     * Execute the actual bits.
     *
     * Should return one of the status constant values, defined as class
     * constants in Executor.
     *
     * @param XML\Node $configuration
     * @param Logger $logger
     * @return int
     */
    public function run( XML\Node $configuration, Logger $logger )
    {
        if ( !isset( $configuration->src ) )
        {
            $logger->log( 'No source provided.', Logger::ERROR );
            return Executor::ERROR;
        }
        $src = (string) $configuration->src;

        if ( !isset( $configuration->dst ) )
        {
            $logger->log( 'No destination provided.', Logger::ERROR );
            return Executor::ERROR;
        }
        $dst = (string) $configuration->dst;

        $depth = -1;
        if ( isset( $configuration->depth ) &&
             is_numeric( (string) $configuration->depth ) )
        {
            $depth = (int) (string) $configuration->depth;
        }

        $this->copyRecursive( $src, $dst, $depth, $logger );
        return Executor::SUCCESS;
    }

    /**
     * Copy files and directories recursively
     *
     * Copy files and directories recursively. I fone operation fails, a
     * warning will be issued and the operation will be continued.
     *
     * A negative depth means infinite recursion. A depth of 1 means that the
     * current files and directories are created, but no recursion is applied.
     *
     * @param string $src
     * @param string $dst
     * @param int $depth
     * @param Logger $logger
     * @return void
     */
    protected function copyRecursive( $src, $dst, $depth, Logger $logger )
    {
        if ( $depth == 0 )
        {
            return;
        }

        // Check if source file exists at all.
        if ( !is_file( $src ) && !is_dir( $src ) )
        {
            $logger->log( "$src is not a valid source.", Logger::WARNING );
            return;
        }

        // Skip non readable files in src directory
        if ( !is_readable( $src ) )
        {
            $logger->log( "$src is not readable, skipping.", Logger::WARNING );
            return;
        }

        // Destination file should not exist
        if ( is_file( $dst ) || is_dir( $dst ) )
        {
            $logger->log( "$dst already exists, and cannot be overwritten.", Logger::WARNING );
            return;
        }

        // Actually copy
        if ( is_dir( $src ) )
        {
            mkdir( $dst );
        }
        elseif ( is_file( $src ) )
        {
            copy( $src, $dst );
            return;
        }

        // Recurse into directory
        $dh = opendir( $src );
        while ( ( $file = readdir( $dh ) ) !== false )
        {
            if ( ( $file === '.' ) ||
                ( $file === '..' ) )
            {
                continue;
            }

            $this->copyRecursive(
                $src . '/' . $file,
                $dst . '/' . $file,
                $depth - 1,
                $logger
            );
        }
    }
}

