<?php
/**
 * Created by IntelliJ IDEA.
 * User: chuxiaofeng
 * Date: 17/5/27
 * Time: 上午10:12
 */

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Contract\Network\Connection;
use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Connection\Exception\AsyncConnectionHasReleasedException;
use Zan\Framework\Network\Connection\Exception\AsyncConnectionIsNotReadyException;
use Zan\Framework\Network\Connection\Exception\GetConnectionTimeoutFromPool;

class AsyncConnection implements Async, Connection
{
    /**
     * @var bool
     */
    public $isReleased;

    /**
     * @var callable
     */
    private $callback;

    /**
     * @var PoolEx
     */
    private $poolEx;

    /**
     * @var \swoole_client|\swoole_redis|\swoole_mysql
     */
    private $connEx;

    public function __construct(PoolEx $poolEx)
    {
        $this->poolEx = $poolEx;
        $this->isReleased = false;
    }

    public function __invoke(\swoole_connpool $pool, $conn)
    {
        if ($cc = $this->callback) {
            if ($conn !== false) {
                $this->connEx = $conn;
                $cc($this);
            } else {
                $cc(null, new GetConnectionTimeoutFromPool("get connection timeout [pool=$this->poolEx->poolType]"));
            }
            $this->callback = null;
        } else {
            // swoole 内部发生同步call异步回调, 不应该发生
            $cc(null, new ZanException("internal error happened in swoole connection pool [pool=$this->poolEx->poolType]"));
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }

    public function getSocket()
    {
        $this->assertReady();
        return $this->connEx;
    }

    public function getEngine()
    {
        return $this->poolEx->poolType;
    }

    public function getConfig()
    {
        return $this->poolEx->config;
    }

    public function release()
    {
        return $this->releaseOnce();
    }

    public function close()
    {
        return $this->releaseOnce(true);
    }

    public function heartbeat() { }

    private function releaseOnce($close = false)
    {
        if ($this->isReleased) {
            return false;
        }

        $this->isReleased = true;
        return $this->poolEx->release($this->connEx, $close);
    }

    private function assertReady()
    {
        if ($this->connEx === null) {
            throw new AsyncConnectionIsNotReadyException("asynchronous connection is not ready [pool=$this->poolEx->poolType]");
        } else if ($this->isReleased) {
            throw new AsyncConnectionHasReleasedException("asynchronous connection has released [pool=$this->poolEx->poolType] ");
        }
    }
}