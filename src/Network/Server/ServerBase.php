<?php

namespace Zan\Framework\Network\Server;

use Zan\Framework\Foundation\Application;
use Zan\Framework\Foundation\Container\Di;
use Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Network\Server\Timer\Timer;

abstract class ServerBase
{

    protected $serverStartItems = [
    ];

    protected $workerStartItems = [
    ];

    public $swooleServer;

    public function __construct($swooleServer, array $config)
    {
        $this->swooleServer = $swooleServer;
        $this->swooleServer->set($config);
    }

    abstract protected function init();
    abstract protected function setSwooleEvent();

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

        // 解决supervisor标准错误重定向文件zend输出无时间戳问题
        if ($workerId === 0 && RunMode::get() === "online") {
            Timer::tick(60 * 1000, function() { sys_error("tick"); });
        }
    }

    public function start()
    {
        $this->setSwooleEvent();

        \swoole_async_set(["socket_dontwait" => 1]);

        $this->bootServerStartItem();
        $this->init();
        $this->swooleServer->start();
    }

    protected function getCustomizedServerStartItems()
    {
        $basePath = Application::getInstance()->getBasePath();
        $configFile = $basePath . '/init/ServerStart/.config.php';

        if (file_exists($configFile)) {
            return include $configFile;
        } else {
            return [];
        }
    }

    protected function getCustomizedWorkerStartItems()
    {
        $basePath = Application::getInstance()->getBasePath();
        $configFile = $basePath . '/init/WorkerStart/.config.php';

        if (file_exists($configFile)) {
            return include $configFile;
        } else {
            return [];
        }
    }

    /**
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
        return;

        $pidFilePath = $this->getPidFilePath();
        if (false === file_put_contents($pidFilePath, $pid)) {
            sys_error("write pid into $pidFilePath failed");
        }
    }
}