#!/usr/bin/env php
<?php
/**
 * Web runner
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
 * This file contains the web runner for periodic
 *
 * It may be called in any interval, and will execute all jobs which are open
 * since the last call. It will output messages in HTML, so it can easily be
 * viewed inside any browser.
 *
 * Configure proper data and task directories directly in this script.
 */

namespace Arbit\Periodic;

$dataDir     = 'data/';
$taskDir     = 'tasks/';
$crontable = <<<EOCRON
# * * * * * myTask
EOCRON;

// Include environment
require __DIR__ . '/../php/Arbit/Periodic/bootstrap.php';

try
{
    // Instantiate executor
    $executor = new Executor(
        $crontable,
        new TaskFactory(
            $taskDir,
            new CommandRegistry()
        ),
        new Logger\Html(),
        $dataDir
    );
    $executor->run();
}
catch ( Exception $e )
{
    header( 'HTTP/1.1 500 Internal Server Error' );
    echo $e->getMessage();
}

