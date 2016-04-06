<?php

namespace Zan\Framework\Network\Server\Timer;

class AfterTimerManager extends BaseTimerManager
{
    /**
     * 获取tick类型的timer的回调包装函数
     *
     * @param TimerJob $timer
     * @param callable $callback
     *
     * @return callable
     */
    public static function getCallback(TimerJob $timer, Callable $callback)
    {
        return function() use ($timer, $callback) {
            /**
             * 仅仅执行一次，需要在回调中清理
             */
            Timer::clearAfterJob($timer->getJobName());
            call_user_func($callback);
        };
    }

    /**
     * {@inheritdoc}
     */
    public static function isExist($jobName)
    {
        return parent::isExist($jobName);
    }

    /**
     * {@inheritdoc}
     */
    public static function addTimer(TimerJob $timer)
    {
        return parent::addTimer($timer);
    }

    /**
     * {@inheritdoc}
     */
    public static function removeTimer(TimerJob $timer)
    {
        return parent::removeTimer($timer);
    }

    /**
     * {@inheritdoc}
     */
    public static function validJobName($jobName)
    {
        return parent::validJobName($jobName);
    }

    /**
     * {@inheritdoc}
     */
    public static function show()
    {
        return parent::show();
    }

    /**
     * {@inheritdoc}
     */
    public static function get($jobName)
    {
        return parent::get($jobName);
    }
}
