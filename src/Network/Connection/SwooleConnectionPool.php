<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/27
 * Time: 上午10:57
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\ConnPoolHeartbeatDelegate;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Connection\Exception\GetConnectionTimeoutFromPool;

class SwooleConnectionPool implements Async/*, ConnectionPool*/
{
    public $poolType;
    /**
     * @var \swoole_conn_pool
     */
    public $pool;

    public $config;

    public $heartbeatHandler;

    public $callback;

    public $typeMap = [
        'Mysqli' => \swoole_conn_pool::SWOOLE_CONNPOOL_MYSQL,
        'Redis' => \swoole_conn_pool::SWOOLE_CONNPOOL_REDIS,
        'Tcp' => \swoole_conn_pool::SWOOLE_CONNPOOL_TCP,
        'Syslog' => \swoole_conn_pool::SWOOLE_CONNPOOL_TCP,
    ];

    public function __construct($factoryType, array $config, ConnPoolHeartbeatDelegate $heartbeatHandler)
    {
        if (!isset($this->typeMap[$factoryType])) {
            throw new InvalidArgumentException("Not Support Swoole Connection Pool Factory Type $factoryType");
        }

        $this->pool = new \swoole_conn_pool($this->typeMap[$factoryType]);
        $this->config = $config;
        $this->poolType = $factoryType;
        $this->heartbeatHandler = $heartbeatHandler;
    }

    public function init()
    {
        $this->pool->on("hbConstruct", $this->heartbeatHandler->onHeartbeatConstruct);
        $this->pool->on("hbeatCheck", $this->heartbeatHandler->onHeartbeatCallback);
        $this->pool->setConnInfo();
        $this->pool->initConnPool();
    }

    public function get($timeout = 0)
    {
        if ($timeout === 0) {
            // TODO
            // $timeout = // get from config
        }

        $r = $this->pool->get($timeout, [$this, "getConnectionDone"]);
        if ($r === false) {
            throw new ZanException("fail to call swoole_conn_pool::get ");
        }

        yield $this;
    }

    public function release($conn, $error = false)
    {
        $status = $error ? \swoole_conn_pool::SWOOLE_CONNOBJ_CONNERR : \swoole_conn_pool::SWOOLE_CONNOBJ_CONNECTED;
        return $this->pool->release($conn, $status);
    }

    public function getConnectionDone($result, $conn)
    {
        if ($cc = $this->callback) {
            if ($result) {
                $poolConnection = new SwoolePoolConnection($conn, $this);
                $cc($poolConnection);
            } else {
                $cc(null, new GetConnectionTimeoutFromPool("get connection timeout, $this"));
            }
        } else {
            // swoole 内部发生同步call异步回调, 不应该发生
            assert(false);
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    public function __toString()
    {
        // TODO 打包配置信息

        // TODO: Implement __toString() method.
    }
}