<?php

namespace Zan\Framework\Sdk\Timer;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Timer
{
    /**
     * @var int the id of timer
     */
    private $timerId;

    /**
     * @var string type of timer, between tick and after
     */
    private $type = '';

    /**
     * @var string the single string for each timer
     */
    private $hash = '';

    /**
     * 添加一个每隔 {$interval} 毫秒 执行一次的计时器任务
     * @param int        $interval  单位: 毫秒
     * @param callable   $callback
     * @param array|null $params
     *
     * @return string timer hash
     */
    public static function tick($interval, Callable $callback, $params = null)
    {
        self::valid($interval);
        $timer = new self();
        $timerId = swoole_timer_tick($interval, TimerManager::getTickCallback($timer, $callback), $params);
        $timer->setTimerId($timerId);
        $timer->setType(__FUNCTION__);
        return TimerManager::addTimer($timer);
    }

    /**
     * 添加一个 {$interval} 毫秒后仅执行一次的计时器任务
     * @param int        $interval  单位: 毫秒
     * @param callable   $callback
     * @param array|null $params
     *
     * @return string timer hash
     */
    public static function after($interval, Callable $callback, $params = null)
    {
        self::valid($interval);
        $timer = new self();
        $timerId = swoole_timer_after($interval, TimerManager::getAfterCallback($timer, $callback), $params);
        $timer->setTimerId($timerId);
        $timer->setType(__FUNCTION__);
        return TimerManager::addTimer($timer);
    }

    /**
     * 根据timer hash 清除一个计时器任务
     *
     * @param string $hash
     *
     * @return bool
     */
    public static function clear($hash)
    {
        if (!TimerManager::validHash($hash)) {
            return false;
        }

        $timer = TimerManager::get($hash);
        if (!$timer instanceof static) {
            return false;
        }

        $isCleared = swoole_timer_clear($timer->getTimerId());

        $isRemoved = $isCleared ? TimerManager::removeTimer($timer) : false;

        return $isCleared and $isRemoved;
    }

    /**
     * set timer id of timer
     * @param $timerId
     */
    public function setTimerId($timerId)
    {
        $this->timerId = $timerId;
    }

    /**
     * get the id of timer
     *
     * @return int
     */
    public function getTimerId()
    {
        return $this->timerId;
    }

    /**
     * set the type of timer
     *
     * @param $type
     *
     * @throws InvalidArgumentException
     */
    public function setType($type)
    {
        if (!in_array($type, ['tick', 'after'])) {
            throw new InvalidArgumentException('type must be tick or after!');
        }

        $this->type = $type;
    }

    /**
     * get the type of timer
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * set the single string hash
     *
     * @param $hash
     *
     * @throws InvalidArgumentException
     */
    public function setHash($hash)
    {
        if (empty($hash)) {
            throw new InvalidArgumentException('hash must be not empty!');
        }
        $this->hash = $hash;
    }

    /**
     * get the hash of timer
     *
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    final private function __construct() {}

    private static function valid($interval)
    {
        if (!is_numeric($interval) or is_float($interval)) {
            throw new InvalidArgumentException('interval must be a int!');
        }
    }
}
