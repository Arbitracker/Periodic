<?php

class periodicTestLogger implements periodicLogger
{
    public $logMessages = array();
    public $task        = null;
    public $command     = null;

    public function log( $message, $severity = periodicLogger::INFO )
    {
        $mapping = array(
            periodicLogger::INFO    => "i",
            periodicLogger::WARNING => "W",
            periodicLogger::ERROR   => "E",
        );
        $msg = '(' . $mapping[$severity] . ') ';

        if ( $this->task )    $msg .= "[{$this->task}] ";
        if ( $this->command ) $msg .= "[{$this->command}] ";

        $this->logMessages[] = $msg . $message;
    }

    public function setTask( $task = null )
    {
        $this->task = $task;
    }

    public function setCommand( $command = null )
    {
        $this->command = $command;
    }
}

