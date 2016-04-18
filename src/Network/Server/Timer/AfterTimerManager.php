<?php

namespace Zan\Framework\Network\Server\Timer;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class AfterTimerManager extends TimerManagerAbstract
{
    use Singleton;

    /**
     * 获取tick类型的timer的回调包装函数
     *
     * @param String $jobId
     * @param Callable $callback
     *
     * @return Callable
     */
    public static function getCallback($jobId, Callable $callback)
    {
        return function() use ($jobId, $callback) {
            /**
             * 仅仅执行一次，需要在回调中清理
             */
            AfterTimerManager::getInstance()->delete($jobId);
            call_user_func($callback);
        };
    }
}
