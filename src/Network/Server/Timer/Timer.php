<?php

namespace Zan\Framework\Network\Server\Timer;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Server\Timer\Exceptions\TimerExistException;

class Timer
{
    /**
     * 添加一个每隔 {$interval} 毫秒 执行一次的计时器任务
     * @param int        $interval  单位: 毫秒
     * @param string     $jobName   标识任务的唯一标识符，必须唯一
     * @param callable   $callback
     *
     * @return string               timer hash
     *
     * @throws InvalidArgumentException
     * @throws TimerExistException
     */
    public static function tick($interval, $jobName, Callable $callback)
    {
        self::valid($interval);

        if (TickTimerManager::isExist($jobName)) {
            throw new TimerExistException('job name is exist!');
        }

        $timer = new TimerJob(__FUNCTION__, $jobName);
        $timerId = swoole_timer_tick($interval, TickTimerManager::getCallback($timer, $callback));
        $timer->setTimerId($timerId);

        return TickTimerManager::addTimer($timer);
    }

    /**
     * 添加一个 {$interval} 毫秒后仅执行一次的计时器任务
     * @param int        $interval  单位: 毫秒
     * @param string     $jobName   标识任务的唯一标识符，必须唯一
     * @param callable   $callback
     *
     * @return string timer hash
     *
     * @throws InvalidArgumentException
     * @throws TimerExistException
     */
    public static function after($interval, $jobName, Callable $callback)
    {
        self::valid($interval);

        if (AfterTimerManager::isExist($jobName)) {
            throw new TimerExistException('job name is exist!');
        }

        $timer = new TimerJob(__FUNCTION__, $jobName);
        $timerId = swoole_timer_after($interval, AfterTimerManager::getCallback($timer, $callback));
        $timer->setTimerId($timerId);

        return AfterTimerManager::addTimer($timer);
    }

    /**
     * 根据tick timer job name 清除一个计时器任务
     *
     * @param string $jobName
     *
     * @return bool
     */
    public static function clearTickJob($jobName)
    {
        if (!TickTimerManager::validJobName($jobName) or !TickTimerManager::isExist($jobName)) {
            return false;
        }

        /** @var TimerJob $timer */
        $timer = TickTimerManager::get($jobName);

        $isCleared = swoole_timer_clear($timer->getTimerId());

        $isRemoved = $isCleared ? TickTimerManager::removeTimer($timer) : false;

        return $isCleared and $isRemoved;
    }

    /**
     * 根据after timer job name 清除一个计时器任务
     *
     * @param string $jobName
     *
     * @return bool
     */
    public static function clearAfterJob($jobName)
    {
        if (!AfterTimerManager::validJobName($jobName) or !AfterTimerManager::isExist($jobName)) {
            return false;
        }

        /** @var TimerJob $timer */
        $timer = AfterTimerManager::get($jobName);

        $isCleared = swoole_timer_clear($timer->getTimerId());

        $isRemoved = $isCleared ? AfterTimerManager::removeTimer($timer) : false;

        return $isCleared and $isRemoved;
    }

    private static function valid($interval)
    {
        if (!is_numeric($interval) or is_float($interval)) {
            throw new InvalidArgumentException('interval must be a int!');
        }
    }
}
