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

/**
 * Command
 *
 * Command to (recursively) copy files.
 */
class periodicFilesystemCopyCommand extends periodicCommand
{
    /**
     * Run command
     *
     * Execute the actual bits.
     *
     * Should return one of the status constant values, defined as class
     * constants in periodicCommand.
     *
     * @return int
     */
    public function run()
    {
        if ( !isset( $this->configuration->src ) )
        {
            $this->logger->log( 'No source provided.', periodicLogger::ERROR );
            return periodicExecutor::ERROR;
        }
        $src = (string) $this->configuration->src;

        if ( !isset( $this->configuration->dst ) )
        {
            $this->logger->log( 'No destination provided.', periodicLogger::ERROR );
            return periodicExecutor::ERROR;
        }
        $dst = (string) $this->configuration->dst;

        $depth = -1;
        if ( isset( $this->configuration->depth ) &&
             is_numeric( (string) $this->configuration->depth ) )
        {
            $depth = (int) (string) $this->configuration->depth;
        }

        $this->copyRecursive( $src, $dst, $depth );
        return periodicExecutor::SUCCESS;
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
     * @return void
     */
    protected function copyRecursive( $src, $dst, $depth )
    {
        if ( $depth == 0 )
        {
            return;
        }

        // Check if source file exists at all.
        if ( !is_file( $src ) && !is_dir( $src ) )
        {
            $this->logger->log( "$src is not a valid source.", periodicLogger::WARNING );
            return;
        }

        // Skip non readable files in src directory
        if ( !is_readable( $src ) )
        {
            $this->logger->log( "$src is not readable, skipping.", periodicLogger::WARNING );
            return;
        }

        // Destination file should not exist
        if ( is_file( $dst ) || is_dir( $dst ) )
        {
            $this->logger->log( "$dst already exists, and cannot be overwritten.", periodicLogger::WARNING );
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
                $depth - 1
            );
        }
    }
}

