<?php

namespace Zan\Framework\Network\Server;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Network\Server\WorkerStart\InitializeConnectionPool;

class ServerBase
{
    protected $serverStartItems = [
    ];

    protected $workerStartItems = [
        InitializeConnectionPool::class,
    ];

    protected function bootServerStartItem()
    {
        $serverStartItems = array_merge(
            $this->serverStartItems,
            $this->getCustomizedServerStartItems()
        );

        foreach ($serverStartItems as $bootstrap) {
            Di::make($bootstrap)->bootstrap($this);
        }
    }

    protected function bootWorkerStartItem($workerId)
    {
        $workerStartItems = array_merge(
            $this->workerStartItems,
            $this->getCustomizedWorkerStartItems()
        );

        foreach ($workerStartItems as $bootstrap) {
            Di::make($bootstrap)->bootstrap($this, $workerId);
        }
    }

    protected function getCustomizedServerStartItems()
    {
        $basePath = Application::getInstance()->getBasePath();
        $configFile = $basePath . '/init/ServerStart/config.php';

        if (file_exists($configFile)) {
            return include $configFile;
        } else {
            return [];
        }
    }

    protected function getCustomizedWorkerStartItems()
    {
        $basePath = Application::getInstance()->getBasePath();
        $configFile = $basePath . '/init/WorkerStart/config.php';

        if (file_exists($configFile)) {
            return include $configFile;
        } else {
            return [];
        }
    }
}