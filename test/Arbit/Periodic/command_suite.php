<?php
/**
 * Periodic command suite
 *
 * @version $Revision: 999 $
 * @license LGPLv3
 */

/**
 * Command tests
 */
require 'command/file_copy.php';
require 'command/file_remove.php';
require 'command/system_exec.php';

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
        $this->addTest( periodicCommandSystemExecTests::suite() );
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

