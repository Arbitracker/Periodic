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

namespace Arbit\Periodic\Command\System;

use Arbit\Periodic\Command,
    Arbit\Periodic\Executor;
    Arbit\Periodic\Logger;

/**
 * Command
 *
 * Command to execute system commands
 */
class Exec extends Command
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
        $command = (string) $this->configuration;
        if ( empty( $command ) )
        {
            $this->logger->log( 'No command provided for execution.', Logger::ERROR );
            return Executor::ERROR;
        }

        // Check for availability of PHP command execution functions
        if ( !function_exists( 'proc_open' ) )
        {
            $this->logger->log( 'Required PHP functions proc_* not available.', Logger::ERROR );
            return Executor::ERROR;
        }

        $failOnError = true;
        if ( isset( $this->configuration['failOnError'] ) )
        {
            $failOnError = !( (string) $this->configuration['failOnError'] === 'false' );
        }

        return $this->execute( $command, $failOnError );
    }

    /**
     * Execute command
     *
     * Execute given shell command. All error output from the shell command
     * will be added as warnings to the logger.
     *
     * If the command returns with an non-zero exit code and $failOnError is
     * set to true the command will return Executor::ERROR - otherwise
     * it will always return with Executor::SUCCESS.
     *
     * @param string $command
     * @param bool $failOnError
     * @return int
     */
    protected function execute( $command, $failOnError = true )
    {
        $descriptors = array(
            0 => array( 'pipe', 'r' ), // STDIN
            1 => array( 'pipe', 'w' ), // STDOUT
            2 => array( 'pipe', 'w' ), // STDERR
        );

        $proc = proc_open( $command, $descriptors, $pipes );
        if ( !is_resource( $proc ) )
        {
            $this->logger->log( 'Could not start processs.', Logger::ERROR );
            return Executor::ERROR;
        }

        // Add command output as information to log
        $output = trim( stream_get_contents( $pipes[1] ) );
        if ( !empty( $output ) )
        {
            $this->logger->log( $output );
        }
        fclose( $pipes[1] );

        // Add command error output as warnings to log
        $output = trim( stream_get_contents( $pipes[2] ) );
        if ( !empty( $output ) )
        {
            $this->logger->log( $output, Logger::WARNING );
        }
        fclose( $pipes[2] );

        // Receive process return values
        $return = proc_close( $proc );
        $this->logger->log( "Command exited with return value $return" );

        return ( $return && $failOnError ) ? Executor::ERROR : Executor::SUCCESS;
    }
}

