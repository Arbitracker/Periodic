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
 * @subpackage Command
 * @version $Revision: 1006 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\Periodic\Command\System;

use Arbit\Periodic\TestCase,
    Arbit\Periodic\Executor,
    Arbit\Xml;

require_once __DIR__ . '/../../TestCase.php';

class SystemExecTest extends TestCase
{
    public function testEmptyConfiguation()
    {
        $cmd = new Exec();

        $this->assertSame(
            Executor::ERROR,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command/>
                ' ),
                $this->getLogger( array(
                    'No command provided for execution.',
                ) )
            )
        );
    }

    public function testSuccessfullCommandExecution()
    {
        $cmd = new Exec();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>echo "Hello world"</command>
                ' ),
                $this->getLogger( array(
                    'Hello world',
                    'Command exited with return value 0',
                ) )
            )
        );
    }

    public function testFailOnUnknownCommand()
    {
        $cmd = new Exec();

        $this->assertSame(
            Executor::ERROR,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command>some_command_not_available</command>
                ' ),
                $this->getLogger( array(
                    'sh: 1: some_command_not_available: not found',
                    'Command exited with return value 127',
                ) )
            )
        );
    }

    public function testNoFailOnUnknownCommand()
    {
        $cmd = new Exec();

        $this->assertSame(
            Executor::SUCCESS,
            $cmd->run(
                Xml\Document::loadString( '<?xml version="1.0" ?>
                    <command failOnError="false">some_command_not_available</command>
                ' ),
                $this->getLogger( array(
                    'sh: 1: some_command_not_available: not found',
                    'Command exited with return value 127',
                ) )
            )
        );
    }
}
