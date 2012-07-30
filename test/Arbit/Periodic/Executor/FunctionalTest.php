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
 * @version $Revision: 1008 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

namespace Arbit\Periodic\Executor;

use Arbit\Periodic\TestCase,
    Arbit\Periodic\CommandRegistry,
    Arbit\Periodic\TaskFactory;

require_once __DIR__ . '/../TestCase.php';

require_once 'test/Arbit/Periodic/helper/Logger.php';
require_once 'test/Arbit/Periodic/helper/Public.php';

class FunctionalTests extends TestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->taskFactory = new TaskFactory( __DIR__ . '/../_fixtures/tasks/', new CommandRegistry() );
    }

    public function testFullExecutorRun()
    {
        $executor = new \periodicTestAllPublicExecutor(
            "* * * * * functional",
            $this->taskFactory, $logger = new \periodicTestLogger(), $this->tmpDir
        );

        // Set a manual last run date, to keep tests deterministic
        file_put_contents( $this->tmpDir . '/lastRun', strtotime( "2000-01-01 12:23:34" ) );

        // First run, should reschedule the test
        $executor->run();

        // Second run - should run rescheduled test only.
        $executor->run();

        $this->assertEquals(
            array(
                '(i) Aquired lock.',
                '(i) Stored last run time.',
                '(i) Create task \'functional\' for scheduled date \'Sat, 01 Jan 2000 12:24:00 +0100\'.',
                '(i) [functional-1224] Start task execution.',
                '(i) [functional-1224] Create command \'fs.copy\'.',
                '(i) [functional-1224] Execute command \'fs.copy\'.',
                '(i) [functional-1224] Finished command execution.',
                '(i) [functional-1224] Create command \'fs.remove\'.',
                '(i) [functional-1224] Execute command \'fs.remove\'.',
                '(i) [functional-1224] Finished command execution.',
                '(i) [functional-1224] Create command \'system.exec\'.',
                '(i) [functional-1224] Execute command \'system.exec\'.',
                '(i) [functional-1224] [system.exec] Hello world',
                '(i) [functional-1224] [system.exec] Command exited with return value 0',
                '(i) [functional-1224] Finished command execution.',
                '(i) [functional-1224] Finished task execution.',
                '(i) Released lock.',
            ),
            $logger->logMessages
        );
    }
}
