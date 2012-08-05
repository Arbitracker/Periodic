<?php
/**
 * Command registry
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
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

namespace Arbit\Periodic;

use Arbit\XML;

/**
 * Command registry
 *
 * A simple static-only command registry, where commands can be registered with
 * their associated implementations.
 */
class CommandRegistry
{
    /**
     * List of commands with their associated class names of their
     * implementations.
     *
     * @var array
     */
    protected $commands = array();

    public function __construct()
    {
        // Register default commands
        $this->registerCommand( 'fs.copy', new Command\FileSystem\Copy() );
        $this->registerCommand( 'fs.remove', new Command\FileSystem\Remove() );
        $this->registerCommand( 'system.exec', new Command\System\Exec() );
    }

    /**
     * Register new command
     *
     * Register a new command, specified by its name and the associated
     * implementation specified by its class name.
     *
     * @param string $name
     * @param Command $command
     * @return void
     */
    public function registerCommand( $name, Command $command )
    {
        $this->commands[$name] = $command;
    }

    /**
     * Get command
     *
     * Returns the Command object, or false on failure.
     *
     * @param string $command
     * @param Logger $logger
     * @return Command
     */
    public function get( $command, Logger $logger )
    {
        if ( !isset( $this->commands[$command] ) )
        {
            $logger->log(
                "Unknown command '$command'.",
                Logger::ERROR
            );
            return false;
        }

        return $this->commands[$command];
    }
}

