<?php

namespace Zan\Framework\Network\Server\Timer;

class TimerJob
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
    private $jobName = '';

    public function __construct($type, $jobName)
    {
        $this->type = $type;
        $this->jobName = $jobName;
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
     * get the type of timer
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * get the job name of timer
     *
     * @return string
     */
    public function getJobName()
    {
        return $this->jobName;
    }
}
