<?php
/**
 * Periodic cronjob suite
 *
 * @version $Revision$
 * @license LGPLv3
 */

/*
 * Set file whitelist for phpunit
 */
if ( !defined( 'PERIODIC_TEST' ) )
{
    $files = include ( $base = dirname(  __FILE__ ) . '/../src/classes/' ) . 'autoload.php';
    foreach ( $files as $class => $file )
    {
        require_once $base . $file;
    }
}

/**
 * Cronjob tests
 */
require 'cronjob/iterator.php';

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

