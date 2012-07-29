<?php
/**
 * Periodic executor suite
 *
 * @version $Revision$
 * @license LGPLv3
 */

/**
 * Executor tests
 */
require 'executor/base.php';
require 'executor/task.php';
require 'executor/command.php';
require 'executor/functional.php';

/**
* Test suite for Periodic cronjob
*/
class periodicExecutorTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'Executor suite' );

        $this->addTest( periodicExecutorTests::suite() );
        $this->addTest( periodicTaskTests::suite() );
        $this->addTest( periodicCommandFactoryTests::suite() );
        $this->addTest( periodicFunctionalExecutorTests::suite() );
    }

    /**
     * Return test suite
     * 
     * @return periodicExecutorTestSuite
     */
    public static function suite()
    {
        return new periodicExecutorTestSuite( __CLASS__ );
    }
}

