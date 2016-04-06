<?php

namespace Zan\Framework\Network\Server\Timer;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;
use Zan\Framework\Network\Server\Timer\Exceptions\TimerExistException;

class Timer
{
    /**
     * 添加一个每隔 {$interval} 毫秒 执行一次的计时器任务
     * @param int        $interval  单位: 毫秒
     * @param string     $jobId   标识任务的唯一标识符，必须唯一
     * @param callable   $callback
     *
     * @return string    $jobId   timer job id
     *
     * @throws InvalidArgumentException
     * @throws TimerExistException
     */
    public static function tick($interval, $jobId, Callable $callback)
    {
        self::valid($interval);

        $manager = TickTimerManager::getInstance();

        if ($manager->isExist($jobId)) {
            throw new TimerExistException('job name is exist!');
        }

        $timerId = swoole_timer_tick($interval, TickTimerManager::getCallback($jobId, $callback));

        return $manager->add($jobId, $timerId);
    }

    /**
     * 添加一个 {$interval} 毫秒后仅执行一次的计时器任务
     * @param int        $interval  单位: 毫秒
     * @param string     $jobId   标识任务的唯一标识符，必须唯一
     * @param callable   $callback
     *
     * @return string    $jobId timer job id
     *
     * @throws InvalidArgumentException
     * @throws TimerExistException
     */
    public static function after($interval, $jobId, Callable $callback)
    {
        self::valid($interval);

        $manager = AfterTimerManager::getInstance();

        if ($manager->isExist($jobId)) {
            throw new TimerExistException('job name is exist!');
        }

        $timerId = swoole_timer_after($interval, AfterTimerManager::getCallback($jobId, $callback));

        return $manager->add($jobId, $timerId);
    }

    /**
     * 根据tick timer job id 清除一个计时器任务
     *
     * @param string $jobId
     *
     * @return bool
     */
    public static function clearTickJob($jobId)
    {
        $manager = TickTimerManager::getInstance();

        if (!TickTimerManager::validJobName($jobId) or !$manager->isExist($jobId)) {
            return false;
        }

        $timerId = $manager->get($jobId);

        $isCleared = swoole_timer_clear($timerId);

        $isRemoved = $isCleared ? $manager->delete($jobId) : false;

        return $isCleared and $isRemoved;
    }

    /**
     * 根据after timer job id 清除一个计时器任务
     *
     * @param string $jobId
     *
     * @return bool
     */
    public static function clearAfterJob($jobId)
    {
        $manager = AfterTimerManager::getInstance();

        if (!AfterTimerManager::validJobName($jobId) or !$manager->isExist($jobId)) {
            return false;
        }

        $timerId = $manager->get($jobId);

        $isCleared = swoole_timer_clear($timerId);

        $isRemoved = $isCleared ? $manager->delete($jobId) : false;

        return $isCleared and $isRemoved;
    }

    private static function valid($interval)
    {
        if (!is_numeric($interval) or is_float($interval)) {
            throw new InvalidArgumentException('interval must be a int!');
        }
    }
}
