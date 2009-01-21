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
 * @subpackage Core
 * @version $Revision$
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPLv3
 */

/**
 * Periodic bas exception
 */
abstract class periodicException extends Exception
{
}

/**
 * periodicAttributeException 
 * 
 * Thrown if an attribute is accessed in a way which is not allowed.
 */
class periodicAttributeException extends periodicException 
{
    /**
     * The accesed attribute is does not exist 
     */
    const NON_EXISTANT  = 0;

    /**
     * The accessed attribute can not be read
     */
    const READ          = 1;

    /**
     * The accessed attribute can not be written 
     */
    const WRITE         = 2;

    /**
     * Create exception from readability type and attribute name.
     * 
     * @param int $type 
     * @param string $attribute 
     * @return void
     */
    public function __construct( $type, $attribute ) 
    {
        $message = 'The accesed attribute "' . $attribute . '" ';
        
        switch( $type ) 
        {
            case self::NON_EXISTANT:
                $message .= 'does not exist.';
            break;
            case self::READ:
                $message .= 'may not be read.';
            break;
            case self::WRITE:
                $message .= 'may not be written.';
            break;
        }

        parent::__construct( $message );
    }
}

/**
 * Thrown if a the cronjob iterator is initialized with an invalid cron
 * definition 
 */
class periodicInvalidCronjobException extends periodicException {}

