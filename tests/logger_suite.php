<?php
/**
 * Periodic logger suite
 *
 * @version $Revision: 999 $
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

        if ( strpos( $file, '/external/' ) === false )
        {
            PHPUnit_Util_Filter::addFileToWhitelist( $base . $file );
        }

    }

    require 'base_test.php';
}


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

