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

require_once __DIR__ . '/../TestCase.php';

require_once 'test/Arbit/Periodic/helper/regex_exposed_iterator.php';

class periodicCronjobIteratorTests extends TestCase
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
            array( '*/1', '*/7', '1-7/2', '0', '1', '7', '0-7', '0,1', '1,2,3', '0-5,6-7', '0-5,6', '5,6-7' ),
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
            array( '*/0', '*/8', '0-', '0,', ',,', '8', '0-8', '0/*' ),
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

    public static function functionalCronTestsProvider() 
    {
        // Read the list of test files
        $input  = glob( __DIR__ . '/../_fixtures/cronjob/functional_test/Arbit/Periodic/*.input' );
        $output = glob( __DIR__ . '/../_fixtures/cronjob/functional_test/Arbit/Periodic/*.output' );
        
        // Interleave the two arrays to be returned together in each dataset
        $interleaved = array();
        $number      = 0;
        while( count( $input ) !== 0 && count( $output ) !== 0 ) 
        {
            $in  = array_shift( $input );
            $out = array_shift( $output );
            $interleaved[] = array( $number++, $in, $out );
        }
        return $interleaved;
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
        $this->assertSame( true, $iterator->validateColumns( $input ) );
    }

    /**
     * @dataProvider invalidCronProvider
     */
    public function testInvalidCron( $input, $output ) 
    {
        $iterator = new periodicTestRegexExposedCronjobIterator();
        $this->assertEquals( $output, $iterator->validateColumns( $input ) );
    }

    /**
     * @dataProvider functionalCronTestsProvider
     */
    public function testCronFunctional( $number, $inputfile, $outputfile ) 
    {
        if ( ( PHP_INT_SIZE > 4 ) &&
             ( $number >= 34 ) &&
             ( $number <= 42 ) )
        {
            $this->markTestSkipped( 'Does not work on 64bit systems.' );
        }

        // Read the input and output information
        $input  = include( $inputfile );
        $output = file( $outputfile );
        // Trim each line to remove possible misformatting
        $output = array_map( 'trim', $output );

        // Instantiate a new cronjobIterator with the read input data
        $iterator = new periodicCronjobIterator( $input );
        $iterator->startTime = strtotime( '2009-01-01 00:00:01' );

        // Generate the same ammount of timestamps that is available as output
        // data
        $lines = count( $output ) - 1;
//        $lines = 999;
        $comparison = array();
        $comparison[] = date( 'Y-m-d H:i:s l', $iterator->current() );
        for( $i=0; $i<$lines; ++$i ) 
        {
            // For readability inside the output file formatted timestamps are
            // used instead of raw timestamps
            $comparison[] = date( 'Y-m-d H:i:s l', $iterator->next() );
        }

        // Make sure our generated values match the expected ones
        $this->assertEquals( $output, $comparison );
//        file_put_contents( $outputfile, implode( "\n", $comparison ) );
    }
}
