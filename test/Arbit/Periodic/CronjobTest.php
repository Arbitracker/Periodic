<?php
/**
 * This file is part of Periodic.
 *
 * Periodic is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; version 3 of the License.
 *
 * Periodic is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
 * License for * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with Periodic; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Periodic
 * @subpackage Cronjob
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\Periodic;

require_once __DIR__ . '/TestCase.php';

class CronjobTest extends TestCase
{
    public function testSimpleCronLine()
    {
        $cronjob = new Cronjob( '*/15 * * * * task' );
        $this->assertSame( null, $cronjob->group );
        $this->assertSame( 'task', $cronjob->task );
    }

    public function testGroupedTaskCronLine()
    {
        $cronjob = new Cronjob( '*/15 * * * * group:task' );
        $this->assertSame( 'group', $cronjob->group );
        $this->assertSame( 'task', $cronjob->task );
    }

    public static function getInvalidCronLines()
    {
        return array(
            array( '' ),
            array( '* * * * test?' ),
            array( '# * * * * * foo' ),
            array( '* * * * * multiple:groups:task' ),
            array( '* * * * * * to_many_stars' ),
        );
    }

    /**
     * @dataProvider getInvalidCronLines
     * @expectedException \UnexpectedValueException
     */
    public function testInvalidCronLines( $line )
    {
        $cronjob = new Cronjob( $line );
    }
}
