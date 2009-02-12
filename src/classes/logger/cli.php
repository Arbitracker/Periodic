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
class periodicCliLogger extends periodicBaseLogger
{
    /**
     * Mapping of error levels to pipes
     * 
     * @var array
     */
    protected $mapping = array(
        periodicLogger::INFO    => self::STDOUT,
        periodicLogger::WARNING => self::STDERR,
        periodicLogger::ERROR   => self::STDERR,
    );

    /**
     * Do not print any message
     */
    const SILENCE = 0;

    /**
     * Print message to STDOUT
     */
    const STDOUT = 1;

    /**
     * Print message to STDERR
     */
    const STDERR = 2;

    /**
     * Write text to stream
     *
     * Write given text to given stream. Simple wrapper function to make class
     * testable.
     *
     * @param string $stream 
     * @param string $text 
     * @return void
     */
    protected function write( $stream, $text )
    {
        $fp = fopen( $stream, 'a' );
        $fwrite( $fp, $text );
        fclose( $fp );
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
    public function log( $message, $severity = self::INFO )
    {
        if ( !isset( $this->mapping[$severity] ) )
        {
            throw new periodicRuntimeException( "Unknown severity: " . $severity );
        }

        switch ( $pipe = $this->mapping[$severity] )
        {
            case self::SILENCE:
                // Ignore this message
                return;

            case self::STDOUT:
                $stream = 'php://stdout';
                break;

            case self::STDERR:
                $stream = 'php://stderr';
                break;

            default:
                throw new periodicRuntimeException( "Unknown output pipe: " . $pipe );
        }

        // Generate and output error message
        $this->write( $stream, sprintf( "[%s]%s %s: %s\n",
            date( 'Y/m/d-H:i.s' ),
            ( $this->task ? 
                ' (' . $this->task . ( 
                    $this->command ?
                        '::' . $this->command :
                        ''
                ) . ')' :
                ''
            ),
            $this->names[$severity],
            $message
        ) );
    }

    /**
     * Set output pipe for severity
     *
     * Set the designated output pipe for log messages with the givene
     * severity. The available severities are defined in the logger interface
     * and are:
     *
     * - periodicLogger::INFO
     * - periodicLogger::WARNING
     * - periodicLogger::ERROR
     *
     * The available output pipes are:
     *
     * - periodicCliLogger::SILENCE, do not output anything
     * - periodicCliLogger::STDOUT, echo messages to STDOUT
     * - periodicCliLogger::STDERR, echo messages to STDERR
     *
     * @param int $severity 
     * @param int $pipe 
     * @return void
     */
    public function setMapping( $severity, $pipe )
    {
        if ( !isset( $this->mapping[$severity] ) )
        {
            throw new periodicRuntimeException( "Unknown severity: " . $severity );
        }

        if ( ( $pipe !== self::SILENCE ) &&
             ( $pipe !== self::STDOUT ) &&
             ( $pipe !== self::STDERR ) )
        {
            throw new periodicRuntimeException( "Unknown output pipe: " . $pipe );
        }

        $this->mapping[$severity] = $pipe;
    }
}

