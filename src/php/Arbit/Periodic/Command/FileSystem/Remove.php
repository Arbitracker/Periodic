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
    Arbit\Periodic\Executor;
    Arbit\Periodic\Logger;

/**
 * Command
 *
 * Command to (recursively) copy files.
 */
class Remove extends Command
{
    /**
     * Run command
     *
     * Execute the actual bits.
     *
     * Should return one of the status constant values, defined as class
     * constants in Command.
     *
     * @return int
     */
    public function run()
    {
        if ( !isset( $this->configuration->path ) )
        {
            $this->logger->log( 'No path provided.', Logger::ERROR );
            return Executor::ERROR;
        }
        $path = (string) $this->configuration->path;

        $pattern = null;
        if ( isset( $this->configuration->pattern ) )
        {
            $pattern = $this->compilePattern( (string) $this->configuration->pattern );
        }

        $this->removeRecursive( $path, $pattern );
        return Executor::SUCCESS;
    }

    /**
     * Compile file pattern into regular expression
     *
     * Compile a simple file pattern like known from glob into a regular
     * expression to match the file basename.
     *
     * @param string $pattern
     * @return string
     */
    protected function compilePattern( $pattern )
    {
        return '(' . str_replace(
            array(
                '\\*',
                '\\?',
            ),
            array(
                '.*',
                '.',
            ),
            preg_quote( $pattern )
        ) . ')';
    }

    /**
     * Remove files and directories recursively
     *
     * Remove files and directories recursively. I one operation fails, a
     * warning will be issued and the operation will be continued.
     *
     * You may optionally specify a pattern and only files and directories
     * matching that pattern will be removed. If a directory matches the
     * pattern all descendents will also be removed.
     *
     * @param string $path
     * @param mixed $pattern
     * @return void
     */
    protected function removeRecursive( $path, $pattern )
    {
        // Check if source file exists at all.
        if ( !is_file( $path ) && !is_dir( $path ) )
        {
            $this->logger->log( "$path is not a valid source.", Logger::WARNING );
            return;
        }

        // Skip non readable files in src directory
        if ( !is_readable( $path ) )
        {
            $this->logger->log( "$path is not readable, skipping.", Logger::WARNING );
            return;
        }

        // Skip non writeable parent directories
        if ( !is_writeable( $parent = dirname( $path ) ) )
        {
            $this->logger->log( "$parent is not writable, skipping.", Logger::WARNING );
            return;
        }

        $matchesPattern = (
            ( $pattern === null ) ||
            ( preg_match( $pattern, basename( $path ) ) )
        );

        // Handle files
        if ( is_file( $path ) )
        {
            if ( $matchesPattern )
            {
                unlink( $path );
            }
            return;
        }

        // Handle directory contents
        $dh = opendir( $path );
        while ( ( $file = readdir( $dh ) ) !== false )
        {
            if ( ( $file === '.' ) ||
                ( $file === '..' ) )
            {
                continue;
            }

            $this->removeRecursive(
                $path . '/' . $file,
                ( $matchesPattern ? null : $pattern )
            );
        }

        if ( $matchesPattern )
        {
            rmdir( $path );
        }
    }
}

