<?php

namespace Zan\Framework\Network\Server;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\RunMode;

class ServerBase
{
    protected $serverStartItems = [
    ];

    protected $workerStartItems = [
    ];

    protected $masterManagerStartItems = [
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

    protected function bootMasterManagerStartItem()
    {
        $masterManagerStartItems = array_merge(
            $this->masterManagerStartItems,
            $this->getCustomizedMasterManagerStartItems()
        );

        foreach ($masterManagerStartItems as $bootstrap) {
            Di::make($bootstrap)->bootstrap($this);
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

    protected function getCustomizedMasterManagerStartItems()
    {
        $basePath = Application::getInstance()->getBasePath();
        $configFile = $basePath . '/init/MasterManager/config.php';

        if (file_exists($configFile)) {
            return include $configFile;
        } else {
            return [];
        }
    }

    /**
     * @TODO 改成可以支持自定义
     * @return string
     */
    protected function getPidFilePath()
    {
        return '/tmp/' . strtolower(Application::getInstance()->getName()) . '.pid';
    }

    protected function removePidFile()
    {
        $pidFilePath = $this->getPidFilePath();
        if (file_exists($pidFilePath)) {
            unlink($pidFilePath);
        }
    }

    protected function writePid($pid)
    {
        if (RunMode::get() == 'test') {
            $pidFilePath = $this->getPidFilePath();

            file_put_contents($pidFilePath, $pid);
        }
    }
}