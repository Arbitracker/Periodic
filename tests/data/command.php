<?php

class periodicTestDummyCommand extends periodicCommand
{
    public function run()
    {
        $this->logger->log( 'Run test command.' );
        return periodicExecutor::SUCCESS;
    }
}

class periodicTestAbortCommand extends periodicCommand
{
    public function run()
    {
        $this->logger->log( 'Run test abortion command.', periodicLogger::WARNING );
        return periodicExecutor::ABORT;
    }
}

class periodicTestRescheduleCommand extends periodicCommand
{
    public function run()
    {
        $this->logger->log( 'Run test reschedule command.', periodicLogger::WARNING );
        return periodicExecutor::RESCHEDULE;
    }
}

class periodicTestErrorCommand extends periodicCommand
{
    public function run()
    {
        $this->logger->log( 'Run test error command.', periodicLogger::ERROR );
        return periodicExecutor::ERROR;
    }
}

class periodicTestErrorneousCommand extends periodicCommand
{
    public function run()
    {
        $this->logger->log( 'Run command returnin nothing.' );
    }
}

