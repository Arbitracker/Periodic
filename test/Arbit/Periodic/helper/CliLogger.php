<?php

class periodicTestCliLogger extends \Arbit\Periodic\Logger\Cli
{
    public $texts = array(
        'php://stdout' => array(),
        'php://stderr' => array(),
    );

    protected function write( $stream, $text )
    {
        $this->texts[$stream][] = preg_replace( '(^\[[^\]]+\])', '[date]', $text );
    }
}

