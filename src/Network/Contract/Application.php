<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Exception\Handler;
use Zan\Framework\Foundation\Exception\System\InvalidArgument;
use Zan\Framework\Utilities\DesignPattern\Registry;
use \Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Zan;

abstract class Application {
    private $config = [];
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->setAppName($config);
        $this->init();
    }

    protected function init()
    {
        Zan::init();
        $this->initErrorHandler();
        Path::init($this->config);
    }

    protected function setAppName($config)
    {
        if(!isset($config['appName'])){
            throw new InvalidArgument('appName not defined in init.bootstrap file');
        }
        Config::set('appName',$config['appName']);
    }

    protected function initRunMode()
    {
        $cli = Registry::get('cli');
        $runMode = $cli->arguments->get('runMode');
        if($runMode){
            RunMode::setCliInput($runMode);
        }
        RunMode::detect();
    }

    protected function initConfig()
    {
        Config::setConfigPath(Path::getConfigPath());
    }

    protected function initErrorHandler()
    {
        Handler::initErrorHandler();
    }

}
