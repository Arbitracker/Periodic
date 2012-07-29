<?php
/**
 * Periodic logger suite
 *
 * @version $Revision: 999 $
 * @license LGPLv3
 */

/**
 * Logger tests
 */
require 'logger/cli.php';
require 'logger/html.php';

/**
* Test suite for Periodic cronjob
*/
class periodicLoggerTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'Logger suite' );

        $this->addTest( periodicLoggerCliTests::suite() );
        $this->addTest( periodicLoggerHtmlTests::suite() );
    }

    /**
     * Return test suite
     * 
     * @return periodicLoggerTestSuite
     */
    public static function suite()
    {
        return new periodicLoggerTestSuite( __CLASS__ );
    }
}

