<?php

class periodicTestLogger implements periodicLogger
{
    public $logMessages = array();

    public function log( $message, $severity = periodicLogger::INFO )
    {
        $mapping = array(
            periodicLogger::INFO    => "i",
            periodicLogger::WARNING => "W",
            periodicLogger::ERROR   => "E",
        );
        $this->logMessages[] = '(' . $mapping[$severity] . ') ' . $message;
    }

    public function setTask( periodicTask $task )
    {
    }

    public function setCommand( periodicCommand $command )
    {
    }
}

