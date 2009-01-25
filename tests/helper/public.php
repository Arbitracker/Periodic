<?php

class periodicTestAllPublicExecutor extends periodicExecutor
{
    public $crontab;

    public function getJobsSince( $time )
    {
        return parent::getJobsSince( $time );
    }

    public function getLastRun()
    {
        return parent::getLastRun();
    }

    public function storeLastRun()
    {
        return parent::storeLastRun();
    }

    public function aquireLock()
    {
        return parent::aquireLock();
    }

    public function releaseLock()
    {
        return parent::releaseLock();
    }
}

