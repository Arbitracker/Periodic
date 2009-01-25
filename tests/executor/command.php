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

require_once 'tests/helper/logger.php';
require_once 'tests/helper/command.php';

class periodicCommandFactoryTests extends PHPUnit_Framework_TestCase
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public function setUp()
    {
        $this->logger = new periodicTestLogger();
        $this->config = arbitXml::loadString( '<?xml version="1.0" ?><configuration/>' );
    }

    public function testUnknownCommand()
    {
        $this->assertFalse(
            periodicCommandRegistry::factory( 'unknown', $this->config, $this->logger )
        );

        $this->assertEquals(
            array(
                '(E) Unknown command \'unknown\'.',
            ),
            $this->logger->logMessages
        );
    }

    public function testUnknownImplementation()
    {
        periodicCommandRegistry::registerCommand( 'invalid', 'testUnknownClassName' );
        $this->assertFalse(
            periodicCommandRegistry::factory( 'invalid', $this->config, $this->logger )
        );

        $this->assertEquals(
            array(
                '(E) Implementation for command \'invalid\' could not be found.',
            ),
            $this->logger->logMessages
        );
    }

    public function testConstructDummyCommand()
    {
        periodicCommandRegistry::registerCommand( 'test.dummy', 'periodicTestDummyCommand' );
        $this->assertTrue(
            periodicCommandRegistry::factory( 'test.dummy', $this->config, $this->logger ) instanceof periodicTestDummyCommand
        );

        $this->assertEquals(
            array(
                '(i) Create command \'test.dummy\'.',
            ),
            $this->logger->logMessages
        );
    }
}
