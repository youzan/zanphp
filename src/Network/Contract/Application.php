<?php
/*
 *    Copyright 2012-2016 Youzan, Inc.
 *
 *    Licensed under the Apache License, Version 2.0 (the "License");
 *    you may not use this file except in compliance with the License.
 *    You may obtain a copy of the License at
 *
 *        http://www.apache.org/licenses/LICENSE-2.0
 *
 *    Unless required by applicable law or agreed to in writing, software
 *    distributed under the License is distributed on an "AS IS" BASIS,
 *    WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *    See the License for the specific language governing permissions and
 *    limitations under the License.
 */
namespace Zan\Framework\Network\Contract;

use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Foundation\Exception\Handler;
use Zan\Framework\Foundation\Exception\System\InvalidArgument;

abstract class Application {

    protected $config;
    protected $appName;
    protected $rootPath;

    public function __construct($config =[], $appName=null)
    {
        $this->config = $config;

        if (null !== $appName ) {
            $this->setAppName($appName);
        }
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

    protected function createObject()
    {

    }

}
