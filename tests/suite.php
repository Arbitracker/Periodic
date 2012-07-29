<?php
/**
 * Periodic main test suite
 *
 * @version $Revision$
 * @license LGPLv3
 */

/*
 * Set file whitelist for phpunit
 */
require 'base_test.php';

/**
 * Test suites
 */
require 'cronjob_suite.php';
require 'executor_suite.php';
require 'command_suite.php';
require 'logger_suite.php';

/**
* Test suite for Periodic
*/
class periodicTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'periodic - A PHP periodic task scheduler' );

        $this->addTestSuite( periodicCronjobTestSuite::suite() );
        $this->addTestSuite( periodicExecutorTestSuite::suite() );
        $this->addTestSuite( periodicCommandTestSuite::suite() );
        $this->addTestSuite( periodicLoggerTestSuite::suite() );
    }

    /**
     * Return test suite
     * 
     * @return periodicTestSuite
     */
    public static function suite()
    {
        return new periodicTestSuite( __CLASS__ );
    }
}
