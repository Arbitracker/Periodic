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
 * @version $Revision: 977 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Command registry
 *
 * A simple static-only command registry, where commands can be registered with
 * their associated implementations.
 */
final class periodicCommandRegistry
{
    /**
     * List of commands with their associated class names of their
     * implementations. 
     *
     * @var array
     */
    protected static $commands = array(
        // Standard file system operations
        'fs.copy'     => 'periodicFilesystemCopyCommand',
        'fs.move'     => 'periodicFilesystemMoveCommand',
        'fs.delete'   => 'periodicFilesystemDeleteCommand',
        'fs.chown'    => 'periodicFilesystemChownCommand',
        'fs.chmod'    => 'periodicFilesystemChmodCommand',

        // Generic system operations
        'system.exec' => 'periodicSystemExecCommand',
    );

    /**
     * Register new command
     *
     * Register a new command, specified by its name and the associated
     * implementation specified by its class name.
     * 
     * @param string $command 
     * @param string $class 
     * @return void
     */
    public static function registerCommand( $command, $class )
    {
        self::$commands[$command] = $class;
    }

    /**
     * Factory command
     *
     * Return a command object from the command specified by its name and its
     * configuration. The additionally passed logger will be used to log
     * command creation and will be passed to the command object for logging.
     *
     * Returns the created periodicCommand object, or false on failure.
     *
     * @param string $command 
     * @param arbitXmlNode $configuration 
     * @param periodicLogger $logger 
     * @return periodicCommand
     */
    public static function factory( $command, arbitXmlNode $configuration, periodicLogger $logger )
    {
        if ( !isset( self::$commands[$command] ) )
        {
            $logger->log(
                "Unknown command '$command'.",
                periodicLogger::ERROR
            );
            return false;
        }

        if ( !class_exists( $class = self::$commands[$command] ) )
        {
            $logger->log(
                "Implementation for command '$command' could not be found.",
                periodicLogger::ERROR
            );
            return false;
        }

        $logger->log(
            "Create command '$command'.",
            periodicLogger::INFO
        );
        return new $class( $configuration, $logger );
    }
}
