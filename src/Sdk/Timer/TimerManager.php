<?php

namespace Zan\Framework\Sdk\Timer;

use Zan\Framework\Utilities\DesignPattern\Singleton;

class TimerManager
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
    private $tickTimers;

    /**
     * @var $timers Timer[] the list of after timers
     */
    private $afterTimers;

    /**
     * 获取tick类型的timer的回调包装函数
     *
     * @param Timer    $timer
     * @param callable $callback
     *
     * @return callable
     */
    public static function getTickCallback(Timer $timer, Callable $callback)
    {
        return function($timerId, $params) use ($timer, $callback) {
            call_user_func($callback, $timer->getHash(), $params);
        };
    }

    /**
     * 获取after类型的timer的回调包装函数
     *
     * @param Timer    $timer
     * @param callable $callback
     *
     * @return callable
     */
    public static function getAfterCallback(Timer $timer, Callable $callback)
    {
        return function($params) use ($timer, $callback) {
            /**
             * 仅仅执行一次，需要在回调中清理
             */
            Timer::clear($timer->getHash());
            call_user_func($callback, $params);
        };
    }

    /**
     * 添加一个timer到timerManager 返回一个作为timer唯一标识符的hash
     *
     * @param Timer $timer
     *
     * @return string
     */
    public static function addTimer(Timer $timer)
    {
        $hash = self::makeHash($timer);
        $timer->setHash($hash);

        self::getInstance()->add($timer);

        return $hash;
    }

    /**
     * 从timerManager里移除一个timer
     *
     * @param Timer $timer
     *
     * @return bool
     */
    public static function removeTimer(Timer $timer)
    {
        return self::getInstance()->delete($timer);
    }

    /**
     * 生成一个对应timer的唯一的hash 生成标识指定timer的hash
     *
     * @param Timer $timer
     *
     * @return string
     */
    public static function makeHash(Timer $timer)
    {
        return md5($timer->getType() . "_" . $timer->getTimerId());
    }

    /**
     * valid hash
     *
     * @param $hash
     *
     * @return bool
     */
    public static function validHash($hash)
    {
        if (!is_string($hash)) {
            return false;
        }
        if (strlen($hash) != 32) {
            return false;
        }

        return true;
    }

    /**
     * 获取当前进程内所有timer的列表
     *
     * @param $type
     *
     * @return Timer[]
     */
    public static function show($type)
    {
        return self::getInstance()->getTimers($type);
    }

    /**
     * 根据hash获取对应timer，不存在则返回false
     *
     * @param $hash
     *
     * @return bool|Timer
     */
    public static function get($hash)
    {
        return self::getInstance()->getTimer($hash);
    }

    /**
     * add timer in timer list by type
     *
     * @param Timer $timer
     *
     * @return bool
     */
    private function add(Timer $timer)
    {
        if ($timer->getType() == self::TICK_TYPE) {
            $this->tickTimers[$timer->getHash()] = $timer;
            return true;
        }
        if ($timer->getType() == self::AFTER_TYPE) {
            $this->afterTimers[$timer->getHash()] = $timer;
            return true;
        }

        return false;
    }

    /**
     * delete a timer in timer list
     *
     * @param Timer $timer
     *
     * @return bool
     */
    private function delete(Timer $timer)
    {
        if ($timer->getType() == self::TICK_TYPE) {
            unset($this->tickTimers[$timer->getHash()]);
        }
        if ($timer->getType() == self::AFTER_TYPE) {
            unset($this->afterTimers[$timer->getHash()]);
        }

        return !isset($this->tickTimers[$timer->getHash()]);
    }

    /**
     * get timer list by type
     *
     * @param null|string $type
     *
     * @return Timer[]
     */
    private function getTimers($type = null)
    {
        if ($type == self::TICK_TYPE) {
            return $this->tickTimers;
        }
        if ($type == self::AFTER_TYPE) {
            return $this->afterTimers;
        }
        return $this->tickTimers + $this->afterTimers;
    }

    /**
     * get timer by hash
     *
     * @param $hash
     *
     * @return bool|Timer
     */
    private function getTimer($hash)
    {
        if (isset($this->tickTimers[$hash])) {
            return $this->tickTimers[$hash];
        }

        if (isset($this->afterTimers[$hash])) {
            return $this->afterTimers[$hash];
        }

        return false;
    }
}
