<?php

class periodicTestDummyCommand extends periodicCommand
{
    public function run()
    {
        $this->logger->log( 'Run test command.' );
        return periodicExecutor::SUCCESS;
    }
}

