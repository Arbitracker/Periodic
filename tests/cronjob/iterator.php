<?php
/**
 * This file is part of Periodic.
 *
 * Periodic is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the
 * Free Software Foundation; version 3 of the License.
 *
 * Periodic is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public
 * License for * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License 
 * along with Periodic; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Periodic
 * @subpackage Cronjob
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

include_once( dirname( __FILE__ ). '/../data/cronjob/regex_exposed_iterator.php' );

class periodicCronjobIteratorTests extends PHPUnit_Framework_TestCase
{
    public static function suite()
    {
        return new PHPUnit_Framework_TestSuite( __CLASS__ );
    }

    public static function validCronProvider() 
    {
        $crons = array();
        $columns = array( 
            array( '*/1', '*/59', '0-59/2', '1', '59', '0-59', '0,1', '0,1,2', '0-23,42-59', '0-23,42', '23,42-59' ),
            array( '*/1', '*/23', '0-23/2', '1', '23', '0-23', '0,1', '0,1,2', '0-5,6-23', '0-5,6', '5,6-23'),
            array( '*/1', '*/31', '1-31/2', '1', '31', '1-31', '1,2', '1,2,3', '1-23,24-31', '1-23,24', '23,24-31' ),
            array( '*/1', '*/12', '1-12/2', '1', '12', '1-12', '1,2', '1,2,3', '1-5,6-12', '1-5,6', '5,6-12'),
            array( '*/1', '*/7', '1-7/2', '1', '7', '1-7', '1,2', '1,2,3', '1-5,6-7', '1-5,6', '5,6-7' ),
        );

        foreach( $columns as $key => $column ) 
        {
            foreach( $column as $entry ) 
            {
                $cron = array();
                for( $i=0; $i<$key; ++$i ) 
                {
                    $cron[] = '*';
                }
                $cron[] = $entry;
                for( $i=4; $i>$key; --$i ) 
                {
                    $cron[] = '*';
                }
                $crons[] = array( $cron );
            }
        }
        
        return $crons;
    }

    public static function invalidCronProvider() 
    {
        $crons = array();
        $columns = array( 
            array( '*/0', '*/60', '0-', '0,', ',,', '60', '0-60', '0/*' ),
            array( '*/0', '*/24', '0-', '0,', ',,', '24', '0-24', '0/*' ),
            array( '*/0', '*/32', '0-', '0,', ',,', '32', '0-32', '0/*', '0' ),
            array( '*/0', '*/13', '0-', '0,', ',,', '13', '0-13', '0/*', '0' ),
            array( '*/0', '*/8', '0-', '0,', ',,', '8', '0-8', '0/*', '0' ),
        );

        foreach( $columns as $key => $column ) 
        {
            foreach( $column as $entry ) 
            {
                $cron = array();
                for( $i=0; $i<$key; ++$i ) 
                {
                    $cron[] = '*';
                }
                $cron[] = $entry;
                for( $i=4; $i>$key; --$i ) 
                {
                    $cron[] = '*';
                }
                $crons[] = array( $cron, $key );
            }
        }
        
        return $crons;
    }

    public function testThrowExceptionOnInvalidCronjob() 
    {
        try 
        {
            $iterator = new periodicCronjobIterator( 
                array( 
                   '*/0', '*/0', '*/0', '*/0', '*/0'
                )
            );
            $this->fail( 'The expected periodicInvalidCronjobException was not thrown' );
        }
        catch( periodicInvalidCronjobException $e ) 
        {
            // We want to check for this exception
        }
    }

    public function testThrowExceptionOnInvalidCronjobString() 
    {
        try 
        {
            $iterator = periodicCronjobIterator::fromString( '*/0 */0 */0 */0 */0' );
            $this->fail( 'The expected periodicInvalidCronjobException was not thrown' );
        }
        catch( periodicInvalidCronjobException $e ) 
        {
            // We want to check for this exception
        }
    }

    public function testInstantiationByCronjobString() 
    {
            $iterator = periodicCronjobIterator::fromString( '1 2 3 4 *' );
    }

    public function testInstantiationByCronjobArray() 
    {
        $iterator = new periodicCronjobIterator( 
            array( 
               '1', '2', '3', '4', '*'
            )
        );
    }

    /**
     * @dataProvider validCronProvider
     */
    public function testValidCron( $input ) 
    {
        $iterator = new periodicTestRegexExposedCronjobIterator();
        $this->assertTrue( true === $iterator->validateColumns( $input ) );
    }

    /**
     * @dataProvider invalidCronProvider
     */
    public function testInvalidCron( $input, $output ) 
    {
        $iterator = new periodicTestRegexExposedCronjobIterator();
        $this->assertEquals( $output, $iterator->validateColumns( $input ) );
    }
}
