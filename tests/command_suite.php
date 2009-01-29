<?php
/**
 * Periodic command suite
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
 * Command tests
 */
require 'command/file_copy.php';
require 'command/file_remove.php';

/**
* Test suite for Periodic cronjob
*/
class periodicCommandTestSuite extends PHPUnit_Framework_TestSuite
{
    /**
     * Basic constructor for test suite
     * 
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->setName( 'Command suite' );

        $this->addTest( periodicCommandFileCopyTests::suite() );
        $this->addTest( periodicCommandFileRemoveTests::suite() );
    }

    /**
     * Return test suite
     * 
     * @return periodicCommandTestSuite
     */
    public static function suite()
    {
        return new periodicCommandTestSuite( __CLASS__ );
    }
}

