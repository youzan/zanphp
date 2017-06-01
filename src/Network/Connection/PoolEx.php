<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/27
 * Time: 上午10:57
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Exception\ZanException;

class PoolEx
{
    public $poolType;

    /**
     * @var \swoole_connpool
     */
    public $poolEx;

    public $config;

    public $typeMap = [
        'Mysqli'    => \swoole_connpool::SWOOLE_CONNPOOL_MYSQL,
        'Tcp'       => \swoole_connpool::SWOOLE_CONNPOOL_TCP,
        'Syslog'    => \swoole_connpool::SWOOLE_CONNPOOL_TCP,
        'Redis'     => \swoole_connpool::SWOOLE_CONNPOOL_REDIS,
        'KVStore'   => \swoole_connpool::SWOOLE_CONNPOOL_REDIS,
    ];

    public static $engineMapEx = ['Mysqli', 'Tcp', 'Syslog', 'Redis', 'KVStore'];

    public static function support($factoryType)
    {
        return class_exists("swoole_connpool") && in_array($factoryType, static::$engineMapEx, true);
    }

    public function __construct($factoryType, array $config)
    {
        if (!isset($this->typeMap[$factoryType])) {
            throw new InvalidArgumentException("not pool type '$factoryType'");
        }

        $this->poolEx = new \swoole_connpool($this->typeMap[$factoryType]);
        $this->config = $config;
        $this->poolType = $factoryType;

        $this->init();
    }

    private function init()
    {
        $poolConf = $this->config["pool"];
        $conf = $this->config;
        $conf["connectTimeout"] = $this->config["connect_timeout"];

        if (isset($poolConf["heartbeat-construct"]) && isset($poolConf["heartbeat-check"])) {
            $conf["hbIntervalTime"] = $poolConf["heartbeat-time"];
            $conf["hbTimeout"] = $poolConf["heartbeat-timeout"];

            $this->poolEx->on("hbConstruct", $poolConf["heartbeat-construct"]);
            $this->poolEx->on("hbCheck", $poolConf["heartbeat-check"]);
        }

        $r = $this->poolEx->setConfig($conf);
        if ($r === false) {
            throw new InvalidArgumentException("invalid connection pool config, [pool=$this->poolType]");
        }

        $min = $poolConf["minimum-connection-count"];
        $max = $poolConf["maximum-connection-count"];
        $r = $this->poolEx->createConnPool($min, $max);
        if ($r === false) {
            throw new ZanException("create conn pool fail [pool=$this->poolType]");
        }
    }

    public function get()
    {
        $asyncConn = new AsyncConnection($this);

        // 从连接池获取连接的超时时间与建立连接超时时间保持一致
        $timeout = $this->config["connect_timeout"];
        $r = $this->poolEx->get($timeout, $asyncConn);
        if ($r === false) {
            throw new ZanException("get connection fail [pool=$this->poolType]");
        }

        yield $asyncConn;
    }

    public function release($conn, $close = false)
    {
        if ($close) {
            return $this->poolEx->release($conn, \swoole_connpool::SWOOLE_CONNNECT_ERR);
        } else {
            return $this->poolEx->release($conn, \swoole_connpool::SWOOLE_CONNNECT_OK);
        }
    }

    public function getStatInfo()
    {
        $info = $this->poolEx->getStatInfo();

        return [
            "all" => $info["all_conn_obj"],
            "active" => $info["all_conn_obj"] - $info["idle_conn_obj"],
            "free" => $info["idle_conn_obj"],
        ];
    }
}