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

namespace Arbit\Periodic\Logger;

use Arbit\Periodic\TestCase,
    Arbit\Periodic\Logger;

require_once __DIR__ . '/../TestCase.php';

require_once 'test/Arbit/Periodic/helper/Logger.php';

class HtmlTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    protected function logSomething( Logger $logger )
    {
        $logger->log( 'Info 1' );
        $logger->setTask( 'task1' );
        $logger->log( 'Info 2' );
        $logger->setCommand( 'command1' );
        $logger->log( 'Info 3' );
        $logger->setTask();
        $logger->log( 'Warning', Logger::WARNING );
        $logger->log( 'Error', Logger::ERROR );
    }

    public function testDefaultLogging()
    {
        ob_start();
        $logger = new Html();
        $this->logSomething( $logger );
        unset( $logger );

        $this->assertSame(
            preg_replace( '(#babdb6">[^<]+</span>)', '#babdb6">[date]</span>', file_get_contents( 'test/Arbit/Periodic/_fixtures/html_logger_00.html' ) ),
            preg_replace( '(#babdb6">[^<]+</span>)', '#babdb6">[date]</span>', ob_get_clean() )
        );
    }

    /**
     * @expectedException \RuntimeException
     */
    public function testInvalidSeverity()
    {
        try
        {
            ob_start();
            $logger = new Html();
            $logger->log( 'Test', 42 );
        } catch ( \Exception $e ) {
            unset( $logger );
            ob_end_clean();

            throw $e;
        }
    }
}
