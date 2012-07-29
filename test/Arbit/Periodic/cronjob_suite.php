<?php
/**
 * Periodic cronjob suite
 *
 * @version $Revision$
 * @license LGPLv3
 */

/**
 * Cronjob tests
 */
require 'cronjob/iterator.php';
require 'cronjob/cronjob.php';

/**
* Test suite for Periodic cronjob
*/
class periodicCronjobTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'Cronjob suite' );

        $this->addTest( periodicCronjobIteratorTests::suite() );
        $this->addTest( periodicCronjobTests::suite() );
    }

    /**
     * Return test suite
     * 
     * @return periodicCronjobTestSuite
     */
    public static function suite()
    {
        return new periodicCronjobTestSuite( __CLASS__ );
    }
}

