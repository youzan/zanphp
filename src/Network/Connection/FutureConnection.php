<?php

namespace Zan\Framework\Network\Connection;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Foundation\Core\Event;
use Zan\Framework\Network\Connection\Exception\ConnectTimeoutException;
use Zan\Framework\Network\Server\Timer\Timer;

class FutureConnection implements Async
{
    private $connKey = '';
    private $timeout = 0;
    private $taskCallback = null;
    private $connectionManager = null;
    private $pool;
    
    public function __construct($connectionManager, $connKey, $timeout, $pool)
    {
        if(!is_int($timeout)){
            throw new InvalidArgumentException('invalid timeout for Future[Connection]');
        }
        $this->connectionManager = $connectionManager;
        $this->connKey = $connKey;
        $this->timeout = $timeout;
        $this->pool = $pool;
        $pool->waitNum++;
        $this->init();
    }

    public function execute(callable $callback, $task)
    {
        $this->taskCallback = $callback;
    }

    private function init()
    {
        $evtName = $this->connKey . '_free';
        Event::once($evtName,[$this,'getConnection']);

        Timer::after($this->timeout, [$this, 'onConnectTimeout'], $this->getConnectTimeoutJobId());
    }

    public function getConnection()
    {
        Task::execute($this->doGeting());
    }

    public function doGeting()
    {
        try {
            if (!isset($this->taskCallback)) {
                return;
            }

            Timer::clearAfterJob($this->getConnectTimeoutJobId());

            if (isset($this->pool->waitNum) && $this->pool->waitNum > 0) {
                $this->pool->waitNum--;
            }

            $conn = (yield $this->connectionManager->get($this->connKey));
            call_user_func($this->taskCallback, $conn);
            unset($this->taskCallback);

        } catch (\Throwable $t) {
            echo_exception($t);
        } catch (\Exception $ex) {
            echo_exception($ex);
        }
    }

    public function onConnectTimeout() {
        if (!isset($this->taskCallback)) {
            return;
        }

        $evtName = $this->connKey . '_free';
        Event::unbind($evtName, [$this, 'getConnection']);

        if (isset($this->pool->waitNum) && $this->pool->waitNum > 0) {
            $this->pool->waitNum--;
        }

        call_user_func($this->taskCallback, null, new ConnectTimeoutException("future $this->connKey connection connected timeout"));
        unset($this->taskCallback);
    }

    private function getConnectTimeoutJobId()
    {
        return spl_object_hash($this) . '_future_connect_timeout';
    }
}