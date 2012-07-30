<?php
/**
 * Autoload file
 *
 * This file is part of periodic
 *
 * periodic is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Lesser General Public License as published by the Free
 * Software Foundation; version 3 of the License.
 *
 * periodic is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Lesser General Public License for
 * more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with periodic; if not, write to the Free Software Foundation, Inc., 51
 * Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @package Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

namespace Arbit\Periodic;

/**
 * Class providing easy means to access event times based on a given cron
 * string as defined by the vixie-cron daemon.
 */
class CronjobIterator implements \Iterator
{
    /**
     * Attributes of the class
     *
     * @var array
     */
    protected $attributes = array();

    /**
     * Pseudo key index incremented on every step and returned if asked for a
     * key
     *
     * @var int
     */
    protected $keyindex = 0;

    /**
     * A combination of months and days of the current year, which match the
     * given cron criteria. These two values are handled together because the
     * weekday depends on both of them
     *
     * @var array
     */
    protected $monthAndDays = array();

    /**
     * Hours in the current year which match the given cron criteria
     *
     * @var array
     */
    protected $hours = array();

    /**
     * Minutes in the current year which match the given cron criteria
     *
     * @var array
     */
    protected $minutes = array();

    /**
     * The year this iterator was instatiated in. This is needed if the
     * processing of the iterator takes place at a year boundary
     *
     * @var int
     */
    protected $year;

    /**
     * The year offset currently used
     *
     * @var int
     */
    protected $yearOffset = 0;

    /**
     * Constructor of the class taking a five tupel containing the values for
     * minute, hour, day of month, month, day of week in this order particular
     * order
     *
     * @param array $cronjob A five tupel of minute, hour, day of month, month
     * and day of week
     * @return void
     */
    public function __construct( array $cronjob )
    {
        $this->attributes = array(
            'minute'        =>  $cronjob[0],
            'hour'          =>  $cronjob[1],
            'dayOfMonth'    =>  $cronjob[2],
            'month'         =>  $cronjob[3],
            'dayOfWeek'     =>  $cronjob[4],
            'startTime'     =>  null,
        );

        if ( $this->validateColumns( $cronjob ) !== true )
        {
            throw new \UnexpectedValueException( 'The supplied cronjob data is invalid.' );
        }

        $this->year = (int)date( 'Y', $this->getCurrentTime() );
    }

    /**
     * Create a cronjobIterator based a complete cronjob definition line
     *
     * @param mixed $cronjobString The cronjob string to use for creation
     * @return CronjobIterator The cronjobIterator based on the given
     * cronjobString
     */
    public static function fromString( $cronjobString )
    {
        /*
         * @todo: maybe this splitting should be done using a regex to support
         * arbitrary whitespace characters
         */
        return new CronjobIterator(
            explode( ' ', $cronjobString )
        );
    }

    /**
     * Return the current time as a timestamp if the attribute startTime is not
     * set. Otherwise return the specified startTime.
     *
     * @return int Either the current time or the defined startTime.
     */
    protected function getCurrentTime()
    {
        return ( ( $this->attributes['startTime'] === null )
               ? ( time() )
               : ( $this->attributes['startTime'] ) );
    }

    /**
     * Validate all cron columns
     *
     * @param mixed $columns Array of cron columns to be checked
     * @return bool True if the columns are valid. Otherwise boolean false or
     * an interger indicating which column is invalid (zero indexed) is
     * returned.
     */
    protected function validateColumns( $columns )
    {
        $patterns = array(
            '((?P<minute>(?:\*|(?:(?:[0-9]|[1-5][0-9])(?:-(?:[0-9]|[1-5][0-9]))?)(?:,(?:[0-9]|[1-5][0-9])(?:-(?:[0-9]|[1-5][0-9]))?)*)(?:/(?:[1-9]|[1-5][0-9]))?)$)AD',
            '((?P<hour>(?:\*|(?:(?:[0-9]|1[0-9]|2[0-3])(?:-(?:[0-9]|1[0-9]|2[0-3]))?)(?:,(?:[0-9]|1[0-9]|2[0-3])(?:-(?:[0-9]|1[0-9]|2[0-3]))?)*)(?:/(?:[1-9]|1[0-9]|2[0-3]))?)$)AD',
            '((?P<dayOfMonth>(?:\*|(?:(?:[1-9]|[1-2][0-9]|3[0-1])(?:-(?:[1-9]|[1-2][0-9]|3[0-1]))?)(?:,(?:[1-9]|[1-2][0-9]|3[0-1])(?:-(?:[1-9]|[1-2][0-9]|3[0-1]))?)*)(?:/(?:[1-9]|[1-2][0-9]|3[0-1]))?)$)AD',
            '((?P<month>(?:\*|(?:(?:[1-9]|1[0-2])(?:-(?:[1-9]|1[1-2]))?)(?:,(?:[1-9]|1[1-2])(?:-(?:[1-9]|1[1-2]))?)*)(?:/(?:[1-9]|1[1-2]))?)$)AD',
            '((?P<dayOfWeek>(?:\*|(?:(?:[0-7])(?:-(?:[0-7]))?)(?:,(?:[0-7])(?:-(?:[0-7]))?)*)(?:/(?:[1-7]))?)$)AD',
        );

        if ( count( $columns ) !== 5 )
        {
            return false;
        }

        foreach( $columns as $key => $column )
        {
            if ( preg_match( $patterns[$key], $column ) !== 1 )
            {
                return (int)$key;
            }
        }

        return true;
    }

    /**
     * Generate a timetable array containing the timestamps of this cronjob for
     * the currently processed year
     *
     * @param int Offset in correlation to the current year to select the year
     * to process
     *
     * @return void
     */
    protected function generateTimetable( $yearOffset = 0 )
    {
        // Reset the current data arrays
        $this->monthAndDays = array();
        $this->hours        = array();
        $this->minutes      = array();

        $this->yearOffset = $yearOffset;
        $year = $this->year + $this->yearOffset;

        // If we are processing the year we are currently in we will need some
        // extra information ready for filtering events from the past
        if ( $this->yearOffset === 0 )
        {
            $currentMonth = (int)date( 'm', $this->getCurrentTime() );
            $currentDay   = (int)date( 'd', $this->getCurrentTime() );
        }

        // Read the columns and generate lists of possible dates
        $months  = $this->applyStepping(
            $this->extractRange( $this->attributes['month'], 1, 12 ),
            $this->extractStep( $this->attributes['month'] )
        );
        $days  = $this->applyStepping(
            $this->extractRange( $this->attributes['dayOfMonth'], 1, 31 ),
            $this->extractStep( $this->attributes['dayOfMonth'] )
        );
        $this->hours  = $this->applyStepping(
            $this->extractRange( $this->attributes['hour'], 0, 23 ),
            $this->extractStep( $this->attributes['hour'] )
        );
        $this->minutes  = $this->applyStepping(
            $this->extractRange( $this->attributes['minute'], 0, 59 ),
            $this->extractStep( $this->attributes['minute'] )
        );

        // If the current year is processed every month that lies before the
        // current one can be removed because it is definetly an event in the past
        if ( $this->yearOffset === 0 )
        {
            foreach ( $months as $nr => $month )
            {
                if ( $month < $currentMonth )
                {
                    unset( $months[$nr] );
                }
            }
        }

        // Combine the months and days into a single array to be able to handle
        // the dayOfWeek entries appropriately

        // There is one special case. If the dayOfWeek is specified, but the
        // dayOfMonth is not restricted (*), only the matching weekdays will
        // the used. Therefore the following processing can be skipped.
        if ( $this->attributes['dayOfMonth'] !== '*' || $this->attributes['dayOfWeek'] === '*' )
        {
            foreach( $months as $month )
            {
                foreach( $days as $day )
                {
                    // Check if we are in the past
                    if ( $this->yearOffset === 0 )
                    {
                        // It is only useful to check this in the first year ;)
                        if ( $month === $currentMonth ) // Use currentMonth which was stored before
                        {
                            if ( $day < $currentDay )
                            {
                                continue;
                            }

                        }
                    }

                    if ( $this->isValidDate( $year, $month, $day ) === true )
                    {
                        $this->monthAndDays[sprintf(
                            '%02d-%02d',
                            $month,
                            $day
                        )] = true;
                    }
                }
            }
        }

        /*
         * Retrieve every day that matches the given dayOfWeek definition if it
         * is restricted in any way
         */
        if ( $this->attributes['dayOfWeek'] !== '*' )
        {
            $weekdays = $this->applyStepping(
                $this->extractRange( $this->attributes['dayOfWeek'], 0, 7 ),
                $this->extractStep( $this->attributes['dayOfWeek'] )
            );

            // Sanitize the weekday array for later processing by ISO-8601
            // weekday specification
            $weekdays = array_flip( $weekdays );
            if( array_key_exists( 0, $weekdays ) )
            {
                unset( $weekdays[0] );
                $weekdays[7] = true;
            }

            /*
             * To get a list of all dates which lie on the given weekdays we
             * loop through every possible date of the year and check for the
             * weekday. We need to take into account the month restriction
             * though.
             */
            foreach( $months as $month )
            {
                for( $day = 1; $day <= 31; ++$day )
                {
                    // Check if we are in the past
                    if ( $this->yearOffset === 0 )
                    {
                        // It is only useful to check this in the first year ;)
                        if ( $month === $currentMonth ) // Use currentMonth which was stored before
                        {
                            if ( $day < $currentDay )
                            {
                                continue;
                            }

                        }
                    }


                    if ( $this->isValidDate( $year, $month, $day ) !== true )
                    {
                        break;
                    }

                    $isoWeekday = (int)date(
                        'N',
                        strtotime(
                            sprintf(
                                '%d-%02d-%02d',
                                $year,
                                $month,
                                $day
                            )
                        )
                    );
                    if ( array_key_exists( $isoWeekday, $weekdays ) )
                    {
                        $this->monthAndDays[sprintf(
                            '%02d-%02d',
                            $month,
                            $day
                        )] = true;
                    }
                }
            }
        }

        /*
         * Flip keys and values on the monthAndDays array and sort it to be easily
         * processable by foreach in the correct order
         */
        $this->monthAndDays = array_keys( $this->monthAndDays );
        sort( $this->monthAndDays );
    }

    /**
     * Check if the given year, month and day combination is a valid calendar
     * entry
     *
     * @param int $year Year to be checked
     * @param int $month Month to be checked
     * @param int $day Day to be checked
     * @return bool True if it is a valid date false otherwise
     */
    protected function isValidDate( $year, $month, $day )
    {
        // Some basic sanity checking
        if ( $month <= 0 || $month > 12 || $day <= 0 || $day > 31 )
        {
            return false;
        }

        // Check for months with 30 days
        if (   ( $month == 4 || $month == 6 || $month == 9 || $month == 11 )
            && ( $day == 31 ) )
        {
            return false;
        }

        // Check for februaries
        if ( $month == 2 )
        {
            // Februrary has a maximum of 29 dates (in a leap year)
            if ( $day > 29 )
            {
                return false;
            }
            // Check if it is a leap year
            $leap = date( 'L', strtotime( $year . '-01-01' ) );
            if ( $leap === '0' && $day > 28 )
            {
                return false;
            }
        }

        return true;
    }

    /**
     * Take a cron column as argument and return an array containing every item
     * in range of the definition
     *
     * @param mixed $definition Cron column/definition to use for extraction
     * @return array Array containing everything defined in the given range. Or
     * bool false if the range is not restricted And no ranges are specified.
     */
    protected function extractRange( $definition, $min = null, $max=null )
    {
        $resultSet = array();

        if ( substr( $definition, 0, 1 ) === '*' )
        {
            // We need ranges otherwise a full set can not be created
            if ( $min === null || $max === null )
            {
                return false;
            }

            for( $i=$min; $i<=$max; ++$i )
            {
                $resultSet[] = $i;
            }

            return $resultSet;
        }

        // Remove the stepping part if it is available
        if ( ( $position = strpos( $definition, '/' ) ) !== false )
        {
            $definition = substr( $definition, 0, $position );
        }

        // Split the definition into list elements. At least one elements needs
        // to be there
        $ranges = explode( ',', $definition );

        foreach( $ranges as $range )
        {
            // There might be a '-' sign which indicates a real range, split it accordingly.
            $entries = explode( '-', $range );
            // If there is only one entry just add it to the result array
            if ( count( $entries ) === 1 )
            {
                $resultSet[] = (int)$entries[0];
            }
            // If a range is defined it needs to be calculated
            else
            {
                $high = (int)max( $entries );
                $low  = (int)min( $entries );

                for( $i=$low; $i<=$high; ++$i )
                {
                    $resultSet[] = $i;
                }
            }
        }

        return $resultSet;
    }

    /**
     * Extract the stepping defined by a given cron column
     *
     * @param mixed $definition Cron definition to use for stepping extraction
     * @return bool false if the stepping does not exist. Otherwise the step is
     * returned as an int
     */
    protected function extractStep( $definition )
    {
        if ( ( $position = strpos( $definition, '/' ) ) !== false )
        {
            return (int)substr( $definition, $position + 1 );
        }
        return false;
    }

    /**
     * Take a range array and apply a defined stepping to it.
     *
     * @param array $range Range array to apply the stepping to
     * @param int $step Stepping to be applied
     * @return array Array with the given stepping applied
     */
    protected function applyStepping( $range, $step )
    {
        if ( $step === false || $step === 1 )
        {
            return $range;
        }

        foreach ( $range as $value => $tmp )
        {
            if ( ( $value % $step ) !== 0 )
            {
                unset( $range[$value] );
            }
        }
        return array_values( $range );
    }

    /**
     * Return the next timestamp which lies in the future. This function
     * handles the regeneration of the timetable information on year boundaries
     * correctly.
     *
     * @return int Timestamp of the next future event
     */
    protected function getNextFutureTimestamp()
    {
        /*
        * To save time in pregeneration we use the array traversal functions
        * here to create iteratively accessed foreach loops on monthAndDays,
        * hours and minutes
        */

        // These values are only used if we are inside the current year
        // Because they will not change significantly during the loop we are
        // just generating them once.
        if ( $this->yearOffset === 0 )
        {
            $currentHour   = (int)date( 'H', $this->getCurrentTime() );
            $currentMinute = (int)date( 'i', $this->getCurrentTime() );
            $currentDay    = (int)date( 'd', $this->getCurrentTime() );
            $currentMonth  = (int)date( 'm', $this->getCurrentTime() );
        }

        do
        {
            // Initialize all timetable values with their current value
            $minute      = current( $this->minutes );
            $hour        = current( $this->hours );
            $monthAndDay = current( $this->monthAndDays );

            // Advance one step
            $minute = next( $this->minutes );
            if ( $minute === false )
            {
                // We reached the end of the minutes array. Therefore we need
                // to advance hours and reset minutes
                $minute = reset( $this->minutes );
                $hour = next( $this->hours );
                if ( $hour === false )
                {
                    // We reached the end of the hours array. Therefore we need
                    // to advance monthAndDays and reset hours
                    $hour = reset( $this->hours );
                    $monthAndDay = next( $this->monthAndDays );
                    if( $monthAndDay === false )
                    {
                        // We reached the end of monthAndDays. Therefore we
                        // need to generate new tables for the next year.
                        $this->generateTimetable( $this->yearOffset + 1 );

                        // Use the first entry of every timetable array
                        $minute      = reset( $this->minutes );
                        $hour        = reset( $this->hours );
                        $monthAndDay = reset( $this->monthAndDays );
                    }
                }
            }

            /*
             * We could use strtotime and just check against the timestamp here.
             * Unfortunately this would be slower by factor 3. Therefore this
             * manual checking routine is used
             */
            // Only the current year is of interest everything else is in the
            // future anyway.
            if ( $this->yearOffset === 0 )
            {
                if ( ( $month = (int)substr( $monthAndDay, 0, 2 ) ) === $currentMonth )
                {
                    if ( ( $day = (int)substr( $monthAndDay, 3, 2 ) ) < $currentDay )
                    {
                        continue;
                    }
                    if ( $day === $currentDay )
                    {
                        if ( $hour < $currentHour )
                        {
                            continue;
                        }
                        if ( $hour === $currentHour )
                        {
                            if ( $minute < $currentMinute )
                            {
                                continue;
                            }
                        }
                    }
                }
            }

            $nextElement = strtotime(
                sprintf(
                    '%d-%s %02d:%02d:00',
                    $this->year + $this->yearOffset, $monthAndDay, $hour, $minute
                )
            );

            // The next element has been found, therefore the loop can be
            // broken
            break;

        } while( true );

        return $nextElement;
    }

    /**
     * Iterator interface function returning the current element
     *
     * @return int Current crontimestamp
     */
    public function current()
    {
        $minute      = current( $this->minutes );
        $hour        = current( $this->hours );
        $monthAndDay = current( $this->monthAndDays );

        $currentElement = strtotime(
            sprintf(
                '%d-%s %02d:%02d:00',
                $this->year + $this->yearOffset, $monthAndDay, $hour, $minute
            )
        );

        if ( $currentElement < $this->getCurrentTime() )
        {
            $currentElement = $this->getNextFutureTimestamp();
        }

        return $currentElement;
    }

    /**
     * Iterator interface function returning the next element
     *
     * @return int Next crontimestamp
     */
    public function next()
    {
        ++$this->keyindex;
        return $this->getNextFutureTimestamp();
    }

    /**
     * Iterator interface function returning the current key
     *
     * @return int current iterator key
     */
    public function key()
    {
        return $this->keyindex;
    }

    /**
     * Iterator interface function resetting the interator
     *
     * @return void
     */
    public function rewind()
    {
        /*
         * If we changed the years already we need to recalculate the data for
         * the first one
         */
        if ( $this->yearOffset !== 0 )
        {
            $this->generateTimetable( 0 );
        }
        else
        {
            // Just reset the current array pointers if the year is correct
            reset( $this->minutes );
            reset( $this->hours );
            reset( $this->monthAndDays );
        }
    }

    /**
     * Iterator interface function returning if the current element of the
     * iterator is valid
     *
     * @return bool
     */
    public function valid()
    {
        // There are always more entries
        return true;
    }

    /**
     * Interceptor method to handle writable attributes
     *
     * @param mixed $k
     * @param mixed $v
     * @return void
     */
    public function __set( $k, $v )
    {
        if ( array_key_exists( $k, $this->attributes ) !== true )
        {
            throw new AttributeException( AttributeException::NON_EXISTANT, $k );
        }

        switch( $k )
        {
            case 'startTime':
                // The crontable is minutes based therefore we need to ceil the
                // seconds value to the next full minute if it is not zero
                if ( ( ( $difference =  $v % 60 ) ) !== 0 )
                {
                    $v += 60 - $difference;
                }

                $this->attributes['startTime'] = (int)$v;
                // Set the newly provided year
                $this->year = (int)date( 'Y', $v );
                // The timetable needs to be regenerated
                $this->generateTimetable( 0 );
            break;
            default:
                throw new AttributeException( AttributeException::WRITE, $k );
        }
    }

    /**
     * Interceptor method to handle readable attributes
     *
     * @param mixed $k
     * @return mixed
     */
    public function __get( $k )
    {
        if ( array_key_exists( $k, $this->attributes ) !== true )
        {
            throw new AttributeException( AttributeException::NON_EXISTANT, $k );
        }

        // All existant attributes are readable
        switch( $k )
        {
            default:
                return $this->attributes[$k];
        };
    }
}

