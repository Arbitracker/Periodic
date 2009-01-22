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

/**
 * Cronjob
 *
 * Class containing all information relevant to a single cronjob item.
 */
class periodicCronjob
{   
    /**
     * Cronjob iterator containing the relevant time information
     * 
     * @var periodicCronjobIterator
     */
    public $iterator;

    /**
     * Name of task
     * 
     * @var string
     */
    public $task;

    /**
     * Name of task group, or null, if no group has been assigned.
     * 
     * @var mixed
     */
    public $group;

    /**
     * Construct from a common line in a crontable
     * 
     * @param string $line 
     * @return void
     */
    public function __construct( $line )
    {
        if ( !preg_match( '(^
            (?!>[#;])
            (\\S+)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)\\s+(\\S+)\\s+
            (?:(?P<group>[^:\\s]+):)?(?P<task>[^:\\s]+)\\s*$
        )x', $line, $matches ) )
        {
            throw new periodicInvalidCronjobException( 'Invalid cron table line: ' . $line );
        }

        $this->iterator = new periodicCronjobIterator( array(
            $matches[1],
            $matches[2],
            $matches[3],
            $matches[4],
            $matches[5],
        ) );
        $this->task  = $matches['task'];
        $this->group = empty( $matches['group'] ) ? null : $matches['group'];
    }
}

