<?php
/**
 * Autoload file
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

/**
 * Logger interface
 *
 * Interface which should be implemented by loggers passed to the executor
 */
abstract class Logger
{
    /**
     * Information level log message
     */
    const INFO    = 1;

    /**
     * A logged warning message
     */
    const WARNING = 2;

    /**
     * A logged error message
     */
    const ERROR   = 4;

    /**
     * Association of Names to log message severities
     *
     * @var array
     */
    protected $names = array(
        Logger::INFO    => 'Info',
        Logger::WARNING => 'Warning',
        Logger::ERROR   => 'Error',
    );

    /**
     * Currently logged task
     *
     * @var string
     */
    protected $task;

    /**
     * Currently logged command
     *
     * @var string
     */
    protected $command;

    /**
     * Set current task
     *
     * Set the currently active task. To reset, just call woithout any
     * parameters.
     *
     * @param string $task
     * @return void
     */
    public function setTask( $task = null )
    {
        $this->task = $task;
    }

    /**
     * Set current command
     *
     * Set the currently active command. To reset, just call woithout any
     * parameters.
     *
     * @param string $command
     * @return void
     */
    public function setCommand( $command = null )
    {
        $this->command = $command;
    }

    /**
     * Log message
     *
     * Log a message, while the message must be convertable into a string.
     * Optionally a log message severity can be specified.
     *
     * @param string $message
     * @param int $severity
     * @return void
     */
    abstract public function log( $message, $severity = self::INFO );
}

