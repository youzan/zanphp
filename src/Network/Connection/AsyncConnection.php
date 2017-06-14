<?php

namespace Zan\Framework\Network\Connection;


use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Exception\ZanException;
use Zan\Framework\Network\Connection\Exception\GetConnectionTimeoutFromPool;

class AsyncConnection implements Async
{
    /**
     * @var callable
     */
    private $callback;

    /**
     * @var PoolEx
     */
    private $poolEx;

    public function __construct(PoolEx $poolEx)
    {
        $this->poolEx = $poolEx;
    }

    public function __invoke(\swoole_connpool $pool, $connEx)
    {
        if ($cc = $this->callback) {
            if ($connEx === false) { // 暂时返回false只有超时的情况
                $free = $pool->getStatInfo()["idle_conn_obj"];
                if ($free !== 0) {
                    sys_error("Internal error in connection pool [pool={$this->poolEx->poolType}, free=$free]");
                }
                $cc(null, new GetConnectionTimeoutFromPool("get connection timeout [pool={$this->poolEx->poolType}]"));
            } else {
                $cc(new ConnectionEx($connEx, $this->poolEx));
            }
            $this->callback = null;
        } else {
            // swoole 内部发生同步call异步回调, 不应该发生
            $cc(null, new ZanException("Internal error in connection pool [pool={$this->poolEx->poolType}]"));
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }
}