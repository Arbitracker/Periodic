<?php

class periodicTestLogger implements \Arbit\Periodic\Logger
{
    public $logMessages = array();
    public $task        = null;
    public $command     = null;

    public function log( $message, $severity = \Arbit\Periodic\Logger::INFO )
    {
        $mapping = array(
            \Arbit\Periodic\Logger::INFO    => "i",
            \Arbit\Periodic\Logger::WARNING => "W",
            \Arbit\Periodic\Logger::ERROR   => "E",
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

