#!/usr/bin/env php
<?php
/**
 * Cron runner
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
 * This file contains the corn runner for periodic
 *
 * It may be called in any interval, and will execute all jobs which are open
 * since the last call. It will output messages usable for cron deamons.
 *
 * Error messages and warning will go directly to STDERR, while info messages
 * are supressed by default. When using the -v option, info messages are echoed
 * to STDOUT.
 */

// Include environment, declaring error handler and autoload function
require dirname( __FILE__ ) . '/base.php';

// Read passed command line options.
$options = getopt( 'vh', array( 'verbose', 'help', 'data::', 'tasks::' ) );

// Ensure commands could been parsed
if ( $options === false )
{
    echo "Could not parse passed commands. Try --help for help.\n";
    exit( 1 );
}

// Echo help, if requested
if ( isset( $options['h'] ) ||
     isset( $options['help'] ) )
{
    echo <<<EOHELP
Periodic cron runner

Runs all tasks scheduled since the last call.
Usage: ${argv[0]} [options] crontab

Options:

-h / --help     Display this help output
-v / --verbose  Verbose output, print info messages

--data=..       Specify data directory
--tasks=..      Specify tasks directory

EOHELP;
    exit( 0 );
}

try
{
    // Instantiate logger
    $logger = new periodicCliLogger();

    // Set logger verbose, if requested
    if ( !isset( $options['v'] ) &&
         !isset( $options['verbose'] ) )
    {
        $logger->setMapping( periodicLogger::INFO, periodicCliLogger::SILENCE );
    }

    // Instantiate task factory
    $taskFactory = new periodicTaskFactory(
        isset( $options['tasks'] ) ? $options['tasks'] : './tasks/'
    );

    $crontab = end( $argv );
    if ( !is_file( $crontab ) ||
         !is_readable( $crontab ) )
    {
        echo "Could not open cron file $crontab.\n";
        exit( 2 );
    }

    // Instantiate executor
    $executor = new periodicExecutor(
        file_get_contents( $crontab ),
        $taskFactory,
        $logger,
        isset( $options['data'] ) ? $options['data'] : './data/'
    );
    $executor->run();
}
catch ( Exception $e )
{
    echo $e->getMessage();
    exit( 3 );
}

