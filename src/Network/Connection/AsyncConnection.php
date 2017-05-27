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

class AsyncConnection implements Async
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

    public function __construct(PoolEx $poolEx)
    {
        $this->poolEx = $poolEx;
        $this->isReleased = false;
    }

    public function __invoke(\swoole_connpool $pool, $connEx)
    {
        if ($cc = $this->callback) {
            if ($connEx !== false) {
                $cc(new ConnectionEx($connEx, $this->poolEx));
            } else {
                $cc(null, new GetConnectionTimeoutFromPool("get connection timeout [pool={$this->poolEx->poolType}]"));
            }
            $this->callback = null;
        } else {
            // swoole 内部发生同步call异步回调, 不应该发生
            $cc(null, new ZanException("internal error happened in swoole connection pool [pool={$this->poolEx->poolType}]"));
        }
    }

    public function execute(callable $callback, $task)
    {
        $this->callback = $callback;
    }
}