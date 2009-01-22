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
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Command
 *
 * Abstract command base class.
 *
 * Commands are the actually execution implementations for all parts of tasks.
 */
abstract class periodicCommand
{
    /**
     * Logger
     * 
     * @var periodicLogger
     */
    protected $logger;

    /**
     * Command configuration
     * 
     * @var arbitXmlNode
     */
    protected $configuration;

    /**
     * Construct command
     *
     * Construct command from its configuration and the currently used logger
     * 
     * @param arbitXmlNode $configuration 
     * @param periodicLogger $logger 
     * @return void
     */
    public function __construct( arbitXmlNode $configuration, periodicLogger $logger )
    {
        $this->configuration = $configuration;
        $this->logger        = $logger;
    }

    /**
     * Run command
     *
     * Execute the actual bits.
     *
     * Should return one of the status constant values, defined as class
     * constants in periodicCommand.
     * 
     * @return int
     */
    abstract public function run();
}

