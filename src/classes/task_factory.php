<?php
/**
 * Task factory
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

/**
 * Task factory
 *
 * Creates task objects from taks name and date.
 */
class periodicTaskFactory
{
    /**
     * Location of task definition files
     * 
     * @var string
     */
    protected $definitions;

    /**
     * Command registry
     *
     * @var periodicCommandRegistry
     */
    protected $commandRegistry;

    /**
     * Construct task factory
     *
     * Construct task factory from the location where the task definitions
     * resides.
     * 
     * @param periodicCommandRegistry $commandRegistry 
     * @return void
     */
    public function __construct( $definitions, periodicCommandRegistry $commandRegistry )
    {
        $this->definitions     = $definitions;
        $this->commandRegistry = $commandRegistry;
    }

    /**
     * Factory task
     *
     * Create a task from its name and schedule date. The additionally passed
     * logger is also passed to the task.
     *
     * Returns false, if a task could not be created properly.
     * 
     * @param string $task 
     * @param int $date 
     * @param periodicLogger $logger 
     * @return periodicTask
     */
    public function factory( $task, $date, periodicLogger $logger )
    {
        if ( !is_file( $path = $this->definitions . '/' . $task . '.xml' ) ||
             !is_readable( $path ) )
        {
            $logger->log(
                "Error reading definition file for task '$task'",
                periodicLogger::ERROR
            );
            return false;
        }

        try
        {
            $taskDefinition = arbitXml::loadFile( $path );
        }
        catch ( arbitException $e )
        {
            $logger->log(
                "Error parsing definition file for task '$task': " . $e->getMessage(),
                periodicLogger::ERROR
            );
            return false;
        }

        $logger->log(
            "Create task '$task' for scheduled date '" . date( 'r', $date ) . "'.",
            periodicLogger::INFO
        );
        return new periodicTask( $task, $date, $taskDefinition, $this->commandRegistry, $logger );
    }
}

