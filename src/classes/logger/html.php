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
 * @version $Revision: 999 $
 * @license http://www.gnu.org/licenses/lgpl-3.0.txt LGPL
 */

/**
 * Web HTML logger
 *
 * Logger which generates HTML output from the logged messages, so the return
 * value can be viewed using a web browser.
 */
class periodicHtmlLogger extends periodicBaseLogger
{
    /**
     * Colors associated with severities
     * 
     * @var array
     */
    protected $colors = array(
        periodicLogger::INFO    => '#4e9a06',
        periodicLogger::WARNING => '#edd400',
        periodicLogger::ERROR   => '#cc0000',
    );

    /**
     * Construct logger
     *
     * Creates and echos the proper HTML header to embed the further output in.
     * 
     * @return void
     */
    public function __construct()
    {
        echo <<<HTMLHEAD
<?xml version="1.0"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1 Strict//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Periodic - Web runner</title>
</head>
<body>
    <h1>Periodic - Web runner</h1>
    <ul>

HTMLHEAD;
    }

    /**
     * Destruct logger
     *
     * Creates and echos the proper HTML footer.
     * 
     * @return void
     */
    public function __destruct()
    {
        echo <<<HTMLFOOTER
    </ul>
</body>
</html>

HTMLFOOTER;
    }

    /**
     * Log message
     *
     * Log a message, while the message must be convertable into a string.
     * Optionally a log message severity can be specified.
     * 
     * @param string $message 
     * @param int $severity 
     * @return void
     */
    public function log( $message, $severity = self::INFO )
    {
        printf( "        <li>
            <span style=\"color: #babdb6\">%s</span> %s
            <span style=\"color: %s; font-weight: bold;\">%s:</span> %s
        </li>\n",
            date( DATE_RFC1036 ),
            ( $this->task ? 
                $this->task . ( 
                    $this->command ?
                        '::' . $command :
                        ''
                . ' ' ) :
                ''
            ),
            $this->colors[$severity],
            $this->names[$severity],
            $message
        );
        flush();
    }
}

