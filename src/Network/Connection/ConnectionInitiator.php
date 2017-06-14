<?php

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Core\Condition;
use Zan\Framework\Foundation\Core\Config;
use Zan\Framework\Network\Common\DnsClient;
use Zan\Framework\Network\Connection\Factory\NovaClient;
use Zan\Framework\Network\Connection\Factory\Redis;
use Zan\Framework\Network\Connection\Factory\Syslog;
use Zan\Framework\Network\Connection\Factory\Tcp;
use Zan\Framework\Network\Server\Timer\Timer;
use Zan\Framework\Utilities\DesignPattern\Singleton;
use Zan\Framework\Network\Connection\Factory\Http;
use Zan\Framework\Network\Connection\Factory\Mysql;
use Zan\Framework\Utilities\Types\Arr;


class ConnectionInitiator
{
    use Singleton;

    const CONNECT_TIMEOUT = 1000;
    const CONCURRENCY_CONNECTION_LIMIT = 50;

    const HEARTBEAT_INTERVAL = 15 * 1000;
    const HEARTBEAT_TIMEOUT = 1000;

    private $engineMap = [
        'mysqli', 
        'http', 
        'redis', 
        'syslog', 
        'novaClient',
        'kVStore',
        'es',
        'tcp',
    ];

    public $directory = '';

    public $poolName='';

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
        $connectionManager->monitor();
        ReconnectionPloy::getInstance()->init();
        $connectionManager->monitorConnectionNum();
    }

    private function initConfig($config)
    {
        if (!is_array($config)) {
            return;
        }
        foreach ($config as $k=>$cf) {
            if (!isset($cf['engine'])) {
                $poolName = $this->poolName;
                $this->poolName = '' === $this->poolName ? $k : $this->poolName . '.' . $k;
                $this->initConfig($cf);
                $this->poolName = $poolName;
                continue;
            }
            if (!isset($cf['pool']) || empty($cf['pool'])) {
                $this->poolName = '';
                continue;
            }

            $cf = $this->fixConfig($cf);

            //创建连接池
            $dir = $this->poolName;
            $this->poolName = '' === $this->poolName ? $k : $this->poolName . '.' . $k;
            $factoryType = $cf['engine'];
            if (in_array($factoryType, $this->engineMap)) {
                $factoryType = ucfirst($factoryType);
                $cf['pool']['pool_name'] = $this->poolName;
                if (isset($cf['host']) && !filter_var($cf['host'], FILTER_VALIDATE_IP) && !isset($cf["path"])) {
                    $poolName = $this->poolName;
                    $this->host2Ip($cf, $poolName, $factoryType);
                } else {
                    $this->initPool($factoryType, $cf);
                }

                $fileConfigKeys = array_keys($config);
                $endKey = end($fileConfigKeys);
                $this->poolName = $k == $endKey ? '' : $dir;
            }
        }
    }

    private function host2Ip($cf, $poolName, $factoryType)
    {
        DnsClient::lookup($cf['host'], function ($host, $ip) use ($cf, $poolName, $factoryType) {
            if (empty($ip)) {
                sys_error("dns look up failed: ".$cf['host']);
                Timer::after(500, function() use ($cf, $poolName, $factoryType) {
                    $this->host2Ip($cf, $poolName, $factoryType);
                });
            } else {
                $cf['host'] = $ip;
                $cf['pool']['pool_name'] = $poolName;
                $this->initPool($factoryType, $cf);

                Condition::wakeUp(ConnectionManager::$getPoolEvent);
            }
        }, function () use ($cf, $poolName, $factoryType) {
            sys_error("dns look up failed: ".$cf['host']);
            Timer::after(500, function() use (&$cf, $poolName, $factoryType) {
                $this->host2Ip($cf, $poolName, $factoryType);
            });
        });
    }

    private function fixConfig(array $config)
    {
        $config = Arr::merge([
            "connect_timeout" => static::CONNECT_TIMEOUT,
            "pool" => [
                "minimum-connection-count" => 10,
                "maximum-connection-count" => 50,
                "maximum-wait-connection" => static::CONCURRENCY_CONNECTION_LIMIT,
                // heartbeat interval 兼容旧配置
                "heartbeat-time" => static::HEARTBEAT_INTERVAL,
                "heartbeat-timeout" => static::HEARTBEAT_TIMEOUT,
                "get-timeout" => static::CONNECT_TIMEOUT * 2,
            ],
        ], $config);

        // for timer
        $config["connect_timeout"] = intval($config["connect_timeout"]);
        $config["pool"]["heartbeat-time"] = intval($config["pool"]["heartbeat-time"]);
        $config["pool"]["heartbeat-timeout"] = intval($config["pool"]["heartbeat-timeout"]);
        $config["pool"]["get-timeout"] = intval($config["pool"]["get-timeout"]);

        return $config;
    }

    /**
     * @param $factoryType
     * @param $config
     */
    private function initPool($factoryType, $config)
    {
        if (PoolEx::support($factoryType)) {
            switch ($factoryType) {
                case 'Redis':
                    $config = Arr::merge([
                        "pool" => [
                            "heartbeat-construct" => function() { return [ "method" => "ping",  "args" => null, ]; },
                            "heartbeat-check" => function() { return true; },
                        ]
                    ], $config);
                    break;

                case 'Mysqli':
                    $config = Arr::merge([
                        "pool" => [
                            "heartbeat-construct" => function() { return [ "method" => "query",  "args" => "select 1", ]; },
                            "heartbeat-check" => function($_, $r) { return $r !== false; },
                        ]
                    ], $config);
                    break;

                // hb?
                case 'Syslog':
                    break;
                case 'Tcp':
                    break;
            }
            $connectionPool = new PoolEx($factoryType, $config);
        } else {
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
                    $factory = new Mysql($config);
                    break;
                case 'NovaClient':
                    $factory = new NovaClient($config);
                    break;
                case 'Tcp':
                    $factory = new Tcp($config);
                    break;
                default:
                    throw new \RuntimeException("not support connection type: $factoryType");
            }
            $connectionPool = new Pool($factory, $config, $factoryType);
        }

        ConnectionManager::getInstance()->addPool($config['pool']['pool_name'], $connectionPool);
    }
}
