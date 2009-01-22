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
 * @version $Revision: 977 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Peridoc executor
 *
 * Manages the execution of scheduled tasks, as defined by the associated
 * crontab.
 */
class periodicExecutor
{
    /**
     * Crontable, which consists of a list of periodicCronjob objects.
     * 
     * @var array(periodicCronjob)
     */
    protected $crontab;

    /**
     * Logger class, which receives all log messages
     * 
     * @var periodicLogger
     */
    protected $logger;

    /**
     * Directory to store locak and last run information
     * 
     * @var string
     */
    protected $lockDir;

    /**
     * Construct the executor
     *
     * Construct the executor from the given cron table and a logger
     * implementing the periodicLogger interface.
     * 
     * @param string $crontab 
     * @param periodicLogger $logger 
     * @param string $lockDir
     * @return void
     */
    public function __construct( $crontab, periodicLogger $logger, $lockDir )
    {
        $this->parseCrontab( $crontab );
        $this->logger  = $logger;
        $this->lockDir = $lockDir;
    }

    /**
     * Parse given cron table
     *
     * All lines, which are not empty or start with a # or ; character are
     * considered cron lines parsed as such.
     * 
     * @param string $crontab 
     * @return void
     */
    protected function parseCrontab( $crontab )
    {
        $lines = preg_split( '(\r\n|\r|\n)', $crontab );
        $this->crontab = array();
        foreach ( $lines as $line )
        {
            $line = trim( $line );
            if ( !empty( $line ) &&
                 ( $line[0] !== '#' ) &&
                 ( $line[0] !== ';' ) )
            {
                $this->crontab[] = new periodicCronjob( $line );
            }
        }
    }

    /**
     * Execute executor
     *
     * This method will check if there are any tasks to be run since the last
     * check. If this is the first check no tasks will be executed.
     *
     * If there are tasks to execute the method will try to aquire a lock, to
     * ensure that not multiple executors try to execute the same task. If it
     * fails to aquire a lock it will exit and check again the next time
     * called.
     *
     * If the lock could successfully be aquired it will run all scheduled
     * tasks in order and release the lock afterwards.
     * 
     * @return void
     */
    public function run()
    {
        $tasks = $this->getTasksSince( $this->getLastRun() );

        if ( count( $tasks ) &&
             $this->aquireLock() )
        {
            $this->storeLastRun();
            $this->executeTasks( $tasks );
            $this->releaseLock();
        }
    }

    /**
     * Get last run of executor
     *
     * Return the last run of the executor as a unix timestamp. If this the
     * first run of the executor return false.
     * 
     * @return mixed
     */
    protected function getLastRun()
    {
        if ( !is_file( $path = $this->lockDir . '/lastRun' ) )
        {
            return false;
        }

        return (int) file_get_contents( $path );
    }

    /**
     * Store last run time
     *
     * Stores the time and date of the last run of the executor.
     * 
     * @return void
     */
    protected function storeLastRun()
    {
        // Silence warnings, which might be caused by multiple possible
        // failures. We handle and log them anyways.
        if ( !@file_put_contents( $this->lockDir . '/lastRun', time() ) )
        {
            $this->logger->log(
                'Failure storing last run time: ' . ( isset( $php_errormsg ) ? $php_errormsg : 'Unknown error - enable the track_errors ini directive.' ),
                periodicLogger::ERROR
            );
            return;
        }

        $this->logger->log( 'Stored last run time.', periodicLogger::INFO );
    }

    /**
     * Try to aquire lock
     *
     * Try to aquire lock - if successful the method will return true - and
     * false otherwise.
     * 
     * @return bool
     */
    protected function aquireLock()
    {
        // Silence call, since PHP will issue a warning when the file exists.
        // But there is no other way to properly immediately create a lock file
        // only if it does not exist yet.
        $fp = @fopen( $this->lockDir . '/lock', 'x' );

        if ( $fp === false )
        {
            // Aquiring the lock failed.
            return false;
        }

        // Store the lock aquiring time in the lock file so this can be
        // debugged more easily and maybe automotically released after
        // stallement.
        fwrite( $fp, time() );
        fclose( $fp );

        $this->logger->log( 'Aquired lock.', periodicLogger::INFO );
        return true;
    }

    /**
     * Release lock
     *
     * Method to release the aquired lock, after the work has been done.
     * 
     * @return void
     */
    protected function releaseLock()
    {
        // Again silencing the file system operation, because there might be
        // multiple possible reasons to fail and we are handling the error
        // anyways.
        if ( !@unlink( $this->lockDir . '/lock' ) )
        {
            $this->logger->log(
                'Failure releasing lock: ' . ( isset( $php_errormsg ) ? $php_errormsg : 'Unknown error - enable the track_errors ini directive.' ),
                periodicLogger::ERROR
            );
            return;
        }

        $this->logger->log( 'Released lock.', periodicLogger::INFO );
    }
}
