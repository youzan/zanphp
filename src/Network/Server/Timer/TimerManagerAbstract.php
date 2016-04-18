<?php

namespace Zan\Framework\Network\Server\Timer;

abstract class TimerManagerAbstract
{
    /**
     * @var $timers Timer[] the list of tick timers
     */
    protected $timers;

    /**
     * 检查当前job id 是否已经存在一个timer record
     *
     * @param $jobId
     *
     * @return bool
     */
    public function isExist($jobId)
    {
        return isset($this->timers[$jobId]);
    }

    /**
     * 添加一个timer record 到timerManager 返回timer job id
     *
     * @param String $jobId
     * @param Int    $timerId
     *
     * @return String $jobId
     */
    public function add($jobId, $timerId)
    {
        $this->timers[$jobId] = $timerId;

        return $jobId;
    }

    /**
     * 根据job id获取对应timer id，不存在则返回false
     *
     * @param string $jobId
     *
     * @return bool|int
     */
    public function get($jobId)
    {
        return isset($this->timers[$jobId]) ? $this->timers[$jobId] : false;
    }

    /**
     * delete a timer in timer list
     *
     * @param String $jobId
     *
     * @return Bool
     */
    public function delete($jobId)
    {
        unset($this->timers[$jobId]);

        return !isset($this->timers[$jobId]);
    }

    /**
     * 获取当前进程内所有timer的列表
     *
     * @return Timer[]
     */
    public function show()
    {
        return $this->timers;
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
}
