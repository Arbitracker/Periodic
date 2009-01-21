<?php
/**
 * Periodic main test suite
 *
 * @version $Revision: 962 $
 * @license LGPLv3
 */

/*
 * Set file whitelist for phpunit
 */
define( 'PERIODIC_TEST', __FILE__ );
$files = include ( $base = dirname(  __FILE__ ) . '/../src/classes/' ) . 'autoload.php';
foreach ( $files as $class => $file )
{
    require_once $base . $file;

    if ( strpos( $file, '/external/' ) === false )
    {
        PHPUnit_Util_Filter::addFileToWhitelist( $base . $file );
    }
}

/**
 * Test suites
 */
require 'cronjob_suite.php';

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
