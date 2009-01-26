<?php
/**
 * Periodic executor suite
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

        if ( strpos( $file, '/external/' ) === false )
        {
            PHPUnit_Util_Filter::addFileToWhitelist( $base . $file );
        }

    }

    require 'base_test.php';
}

/**
 * Executor tests
 */
require 'executor/base.php';
require 'executor/task.php';
require 'executor/command.php';

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

