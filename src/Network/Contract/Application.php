<?php
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\Handler;
use Zan\Framework\Foundation\Exception\System\InvalidArgument;

abstract class Application {

    protected $config;
    protected $appName;
    protected $rootPath;

    public function __construct($config = [])
    {
        $this->config = $config;
    }

    public function setRootPath($dir=null)
    {
        if (!$dir || !is_dir($dir) ) {
            throw new InvalidArgument('Application root path ({$dir}) is invalid!');
        }
        $this->rootPath = $dir;
    }

    public function setAppName($appName=null)
    {
        if (null === $appName ) {
            return false;
        }
        $this->appName = $appName;
    }

    public function init()
    {
        $this->initProjectConfig();
        $this->initFramework();
        $this->initErrorHandler();
    }

    protected function initProjectConfig()
    {
        Config::init();
        Config::setConfigPath($this->config['config_path']);
    }

    private function initErrorHandler()
    {
        Handler::initErrorHandler();
    }

    protected function initFramework() {

    }


}
