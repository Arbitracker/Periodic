<?php

class periodicTestLogger implements periodicLogger
{
    public $logMessages = array();

    public function log( $message, $severity = self::INFO )
    {
        $this->logMessages[] = $message;
    }

    public function setTask( periodicTask $task )
    {
    }

    public function setCommand( periodicCommand $command )
    {
    }
}

