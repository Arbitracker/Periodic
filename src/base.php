<?php
/**
 * Base file
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
 * @version $Revision: 999 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * This file conatins the autoload definitions and basic error handling
 */
function __autoload( $class )
{
    static $base  = null;
    static $files = null;
    
    if ( $base === null )
    {
        $base  = dirname( __FILE__ ) . '/classes/';
        $files = include $base . 'autoload.php';
    }

    if ( isset( $files[$class] ) )
    {
        require $base . $files[$class];
    }
}

