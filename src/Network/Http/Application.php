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
namespace Zan\Framework\Network\Http;

use \HttpServer;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Http\Filter\FilterLoader;

class Application extends \Zan\Framework\Network\Contract\Application {

    /**
     * @var HttpServer
     */
    private $server;

    private $serverConfKey = 'http.server';

    private $filterConfKey = 'filter';

    public function __construct($config)
    {
        parent::__construct($config);
        $this->init();
    }

    public function init()
    {
        parent::init();
        $this->initHttpServer();
        $this->initFilter();
    }

    public function initHttpServer()
    {
        $config = Config::get($this->serverConfKey);
        $this->server = new HttpServer($config);
        $this->server->init();
    }

    private function initFilter()
    {
        $filters = Config::get($this->filterConfKey);
        FilterLoader::loadFilter($filters);
    }

    public function run()
    {
        $this->server->start();
    }

}