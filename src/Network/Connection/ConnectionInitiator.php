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

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Connection\Factory\KVStore;
use Zan\Framework\Network\Connection\Factory\NovaClient;
use Zan\Framework\Network\Connection\Factory\Redis;
use Zan\Framework\Network\Connection\Factory\Syslog;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\Factory\Http;
use Zan\Framework\Network\Connection\Factory\Mysqli;


class ConnectionInitiator
{
    use Singleton;

    private $engineMap = [
        'mysqli', 
        'http', 
        'redis', 
        'syslog', 
    ];

    public $directory = '';

    public $poolName='';

    public function __construct()
    {
    }

    /**
     * @param $directory
     */
    public function init($directory, $server)
    {
        if(!empty($directory)) {
            $this->directory = $directory;
            $config = Config::get($this->directory);
            $this->initConfig($config);
        }
        $connectionManager = ConnectionManager::getInstance();
        $connectionManager->setServer($server);
    }

    private function initConfig($config)
    {
        if (!is_array($config)) {
            return;
        }
        foreach ($config as $k=>$cf) {
            if (!isset($cf['engine'])) {
                $this->poolName = '' === $this->poolName ? $k : $this->poolName . '.' . $k;
                $this->initConfig($cf);
                continue;
            }
            if (!isset($cf['pool']) || empty($cf['pool'])) {
                $this->poolName = '';
                continue;
            }
            //创建连接池
            $dir = $this->poolName;
            $this->poolName = '' === $this->poolName ? $k : $this->poolName . '.' . $k;
            $factoryType = $cf['engine'];
            if (in_array($factoryType, $this->engineMap)) {
                $factoryType = ucfirst($factoryType);
                $cf['pool']['pool_name'] = $this->poolName;
                $this->initPool($factoryType, $cf);
                $fileConfigKeys = array_keys($config);
                $endKey = end($fileConfigKeys);
                $this->poolName = $k == $endKey ? '' : $dir;
            }
        }
    }

    /**
     * @param $factoryType
     * @param $config
     */
    private function initPool($factoryType, $config)
    {
        switch ($factoryType) {
            case 'Redis':
                $factory = new Redis($config);
                break;
            case 'Syslog':
                $factory = new Syslog($config);
                break;
            case 'Http':
                $factory = new Http($config);
                break;
            case 'Mysqli':
                $factory = new Mysqli($config);
                break;
            default:
                break;
        }
        $connectionPool = new Pool($factory, $config, $factoryType);
        ConnectionManager::getInstance()->addPool($config['pool']['pool_name'], $connectionPool);
    }
}
