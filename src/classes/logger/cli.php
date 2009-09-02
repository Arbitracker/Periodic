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
 * @version $Revision: 999 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Command line logger
 *
 * Logger, which logs messages directly to command line. By default errors and
 * warnings will be logged to STDERR, while info messages are printed to
 * STDOUT.
 */
class periodicEzLogLogger extends periodicBaseLogger
{
    /**
     * Mapping of log severities
     * 
     * @var array
     */
    protected $severityMapping = array(
        periodicLogger::INFO    => ezcLog::INFO,
        periodicLogger::WARNING => ezcLog::WARNING,
        periodicLogger::ERROR   => ezcLog::ERROR,
    );

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
    public function log( $message, $severity = self::INFO )
    {
        $log = ezcLog::getInstance();
        $log->log( $message, $this->severityMapping[$severity] );
    }
}

