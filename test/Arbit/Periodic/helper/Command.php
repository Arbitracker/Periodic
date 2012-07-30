<?php

class periodicTestDummyCommand extends \Arbit\Periodic\Command
{
    public function run()
    {
        $this->logger->log( 'Run test command.' );
        return \Arbit\Periodic\Executor::SUCCESS;
    }
}

class periodicTestAbortCommand extends \Arbit\Periodic\Command
{
    public function run()
    {
        $this->logger->log( 'Run test abortion command.', \Arbit\Periodic\Logger::WARNING );
        return \Arbit\Periodic\Executor::ABORT;
    }
}

class periodicTestRescheduleCommand extends \Arbit\Periodic\Command
{
    public function run()
    {
        $this->logger->log( 'Run test reschedule command.', \Arbit\Periodic\Logger::WARNING );
        return \Arbit\Periodic\Executor::RESCHEDULE;
    }
}

class periodicTestErrorCommand extends \Arbit\Periodic\Command
{
    public function run()
    {
        $this->logger->log( 'Run test error command.', \Arbit\Periodic\Logger::ERROR );
        return \Arbit\Periodic\Executor::ERROR;
    }
}

class periodicTestErrorneousCommand extends \Arbit\Periodic\Command
{
    public function run()
    {
        $this->logger->log( 'Run command returnin nothing.' );
    }
}

