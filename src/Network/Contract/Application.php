<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Core\Debug;
use Zan\Framework\Foundation\Core\Path;
use Zan\Framework\Foundation\Exception\Handler;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Utilities\DesignPattern\Registry;
use \Zan\Framework\Foundation\Core\RunMode;
use Zan\Framework\Zan;

abstract class Application {
    private $config = [];
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->setAppName($config);
    }

    protected function init()
    {
        $this->initFramwork();
        $this->initPath();
        $this->initRunMode();
        $this->initDebug();
        $this->initConfig();
        $this->initErrorHandler();
    }

    protected function setAppName($config)
    {
        if(!isset($config['appName'])){
            throw new InvalidArgumentException('appName not defined in init.bootstrap file');
        }
        Config::set('appName',$config['appName']);
    }

    protected function initFramwork()
    {
    }

    protected function initPath()
    {
        Path::init($this->config);
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

    protected function initDebug()
    {
        $cli = Registry::get('cli');
        $debug = $cli->arguments->get('debug');
        if($debug){
            Debug::setCliInput($debug);
        }
        Debug::detect();
    }

    protected function initConfig()
    {
    }

    protected function initErrorHandler()
    {
        Handler::initErrorHandler();
    }

}
