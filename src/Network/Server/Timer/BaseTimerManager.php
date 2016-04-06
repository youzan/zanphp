<?php

namespace Zan\Framework\Network\Server\Timer;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class BaseTimerManager
{
    use Singleton;

    /**
     * the type of tick timer
     */
    const TICK_TYPE = 'tick';
    /**
     * the type of after timer
     */
    const AFTER_TYPE = 'after';

    /**
     * @var $timers Timer[] the list of tick timers
     */
    protected $timers;

    /**
     * 检查当前job name 是否已经存在一个timer
     *
     * @param $jobName
     *
     * @return bool
     */
    public static function isExist($jobName)
    {
        $timer = static::get($jobName);

        return $timer instanceof TimerJob;
    }

    /**
     * 添加一个timer到timerManager 返回timer job name
     *
     * @param TimerJob $timer
     *
     * @return string
     */
    public static function addTimer(TimerJob $timer)
    {
        static::getInstance()->add($timer);

        return $timer->getJobName();
    }

    /**
     * 从timerManager里移除一个timer
     *
     * @param TimerJob $timer
     *
     * @return bool
     */
    public static function removeTimer(TimerJob $timer)
    {
        return static::getInstance()->delete($timer);
    }

    /**
     * valid jab name
     *
     * @param string $jobName
     *
     * @return bool
     */
    public static function validJobName($jobName)
    {
        if (!is_string($jobName)) {
            return false;
        }

        return true;
    }

    /**
     * 获取当前进程内所有timer的列表
     *
     * @return Timer[]
     */
    public static function show()
    {
        return static::getInstance()->getTimers();
    }

    /**
     * 根据hash获取对应timer，不存在则返回false
     *
     * @param string $jobName
     *
     * @return bool|Timer
     */
    public static function get($jobName)
    {
        return static::getInstance()->getTimer($jobName);
    }

    /**
     * add timer in timer list
     *
     * @param TimerJob $timer
     *
     * @return bool
     */
    protected function add(TimerJob $timer)
    {
        $this->timers[$timer->getJobName()] = $timer;
        return true;
    }

    /**
     * delete a timer in timer list
     *
     * @param TimerJob $timer
     *
     * @return bool
     */
    protected function delete(TimerJob $timer)
    {
        unset($this->timers[$timer->getJobName()]);

        return !isset($this->timers[$timer->getJobName()]);
    }

    /**
     * get timer list
     *
     * @return Timer[]
     */
    protected function getTimers()
    {
        return $this->timers;
    }

    /**
     * get timer by job name
     *
     * @param string $jobName
     *
     * @return bool|Timer
     */
    protected function getTimer($jobName)
    {
        if (isset($this->timers[$jobName])) {
            return $this->timers[$jobName];
        }

        return false;
    }
}
