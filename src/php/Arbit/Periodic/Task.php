<?php
/**
 * Task
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
 * Task
 *
 * Tasks are sets of commands with optional additional configuration to handle
 * the different return states of its contained commands.
 */
class Task
{
    /**
     * Logger
     *
     * @var Logger
     */
    protected $logger;

    /**
     * Command configuration
     *
     * @var XML\Node
     */
    protected $configuration;

    /**
     * Command registry
     *
     * @var CommandRegistry
     */
    protected $commandRegistry;

    /**
     * Name of current task
     *
     * @var string
     */
    protected $name;

    /**
     * Scheduled date of current task
     *
     * @var int
     */
    protected $scheduled;

    /**
     * Task configuration properties
     *
     * @var array
     */
    protected $properties = array(
        'reScheduleTime' => 300,
        'timeout'        => 3600,
    );

    /**
     * Construct command
     *
     * Construct command from its configuration and the currently used logger
     *
     * @param string $name
     * @param int $scheduled
     * @param XML\Node $configuration
     * @param CommandRegistry $commandRegistry
     * @param Logger $logger
     * @return void
     */
    public function __construct( $name, $scheduled, XML\Node $configuration, CommandRegistry $commandRegistry, Logger $logger )
    {
        $this->name            = $name;
        $this->scheduled       = $scheduled;
        $this->configuration   = $configuration;
        $this->commandRegistry = $commandRegistry;
        $this->logger          = $logger;

        // Configure task
        foreach ( $this->properties as $key => $default )
        {
            if ( $this->configuration->config &&
                 $this->configuration->config->$key )
            {
                $this->properties[$key] = (int) (string) $this->configuration->config->$key;
            }
        }
    }

    /**
     * Return task ID
     *
     * @return string
     */
    public function getId()
    {
        return $this->name . '-' . date( 'Hi', $this->scheduled );
    }

    /**
     * Run task
     *
     * Execute the contained commands
     *
     * Returns SUCCESS, if either all commands have been run successfully, or
     * one command intentionally aborted the execution. If one command reports
     * an error or requests reschuduling execution is aborted and this is
     * reported to the executor.
     *
     * @return int
     */
    public function execute()
    {
        foreach ( $this->configuration->command as $config )
        {
            if ( ( $command = $this->commandRegistry->get( $type = $config['type'], $this->logger ) ) === false )
            {
                $this->logger->log(
                    "Failed to instantiate command '$type' - aborting task.",
                    Logger::ERROR
                );
                return Executor::ERROR;
            }

            $this->logger->log( "Execute command '$type'." );
            $this->logger->setCommand( $type );
            try
            {
                $status = $command->run( $config, $this->logger );
            }
            catch ( \Exception $e )
            {
                $this->logger->log( 'Command threw exception: ' . $e->getMessage(), Logger::ERROR );
                $status = 0;
            }
            $this->logger->setCommand();

            switch ( $status )
            {
                case Executor::SUCCESS:
                    $this->logger->log( 'Finished command execution.' );
                    break;

                case Executor::ABORT:
                    $this->logger->log( 'Command aborted execution.' );
                    return Executor::SUCCESS;

                case Executor::ERROR:
                    $this->logger->log( 'Command reported error.', Logger::WARNING );
                    return Executor::ERROR;

                case Executor::RESCHEDULE:
                    $this->logger->log( 'Command requested rescheduled execution.' );
                    return Executor::RESCHEDULE;

                default:
                    $this->logger->log( 'Command returned in unknown state.', Logger::ERROR );
                    return Executor::ERROR;
            }
        }

        return Executor::SUCCESS;
    }

    /**
     * Interceptor for task options
     *
     * @param string $property
     * @return mixed
     */
    public function __get( $property )
    {
        if ( !array_key_exists( $property, $this->properties ) )
        {
            throw new AttributeException( AttributeException::NON_EXISTANT, $property );
        }

        return $this->properties[$property];
    }

    /**
     * Interceptor for task options
     *
     * @param string $property
     * @param mixed $value
     * @return void
     */
    public function __set( $property, $value )
    {
        if ( !array_key_exists( $property, $this->properties ) )
        {
            throw new AttributeException( AttributeException::NON_EXISTANT, $property );
        }

        throw new AttributeException( AttributeException::WRITE, $property );
    }
}

