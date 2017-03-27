<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/3/27
 * Time: 上午10:57
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Connection\Driver\Base;
use Zan\Framework\Network\Connection\Driver\Tcp;
use Zan\Framework\Network\Connection\Exception\GetConnectionTimeoutFromPool;

class SwooleConnectionPool implements Async/*, ConnectionPool*/
{
    /**
     * @var \swoole_conn_pool
     */
    private $pool;

    private $config;

    private $heartbeatHandler;

    private $callback;

    public function __construct($type, array $config, $heartbeatHandler)
    {
        $this->pool = new \swoole_conn_pool($type);
        $this->config = $config;
        $this->heartbeatHandler = $heartbeatHandler;
    }

    public function init()
    {

        $this->pool->initConnPool();
        $this->pool->setConnInfo();
        $this->pool->on("hbConstruct", $this->heartbeatHandler->onHeartbeatConstruct);
        $this->pool->on("hbeatCheck", $this->heartbeatHandler->onHeartbeatCallback);
    }

    public function get($timeout = 0)
    {
        if ($timeout === 0) {
            // TODO
            // $timeout = // get from config
        }

        $r = $this->pool->get($timeout, $this->getConnectionDone($timeout));
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

    private function getConnectionDone($timeout, $type)
    {
        return function($result, $conn) use($timeout, $type) {
            if ($cc = $this->callback) {
                if ($result) {

                    /* @var $connection Base */
                    $connection = null;

                    // 子类 重写 close ...   release...
                    switch (true) {
                        case $conn instanceof \swoole_client:
                            $connection = new Tcp();
                            $connection->setSocket($conn);
                            $connection->setConfig(); // TODO
                            $connection->setPool(); // TODO

                            // $connection->init();
                            break;

                        case $conn instanceof \swoole_mysql:
                            return true;


                        case $conn instanceof \swoole_redis:
                            return true;


                        case $conn instanceof \swoole_http_client:
                            return true;


                        default:
                            assert(false);
                    }


                    $cc($connection);

                } else {
                    $cc(null, new GetConnectionTimeoutFromPool("get connection timeout, [pool_type=$type, timeout=$timeout]"));
                }
            } else {
                // swoole 内部发生同步call异步回调, 不应该发生
                assert(false);
            }
        };
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }
}