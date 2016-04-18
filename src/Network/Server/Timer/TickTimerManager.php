<?php

namespace Zan\Framework\Network\Server\Timer;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class TickTimerManager extends TimerManagerAbstract
{
    use Singleton;

    /**
     * 获取tick类型的timer的回调包装函数
     *
     * @param String $jobId
     * @param Callable $callback
     *
     * @return callable
     */
    public static function getCallback($jobId, Callable $callback)
    {
        return function() use ($jobId, $callback) {
            call_user_func($callback, $jobId);
        };
    }
}
