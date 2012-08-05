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
 * @subpackage Executor
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\Periodic;

use Arbit\Xml;

require_once __DIR__ . '/TestCase.php';

class CommandRegistryTest extends TestCase
{
    public function testUnknownCommand()
    {
        $commandRegistry = new CommandRegistry();
        $this->assertFalse(
            $commandRegistry->get(
                'unknown',
                $this->getLogger( array(
                    'Unknown command \'unknown\'.',
                ) )
            )
        );
    }

    public function testConstructDummyCommand()
    {
        $commandRegistry = new CommandRegistry();
        $commandRegistry->registerCommand( 'test.dummy', $this->getSuccessfulCommand() );
        $this->assertTrue(
            $commandRegistry->get(
                'test.dummy',
                $this->getLogger( array(
                ) )
            ) instanceof Command
        );
    }
}

