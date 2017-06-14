<?php

namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Utilities\DesignPattern\Context;

class Task
{
    protected $taskId = 0;
    protected $parentTask;
    protected $coroutine = null;
    protected $context = null;

    protected $sendValue = null;
    protected $scheduler = null;
    protected $status = 0;

    public static function execute($coroutine, Context $context = null, $taskId = 0, Task $parentTask = null)
    {
        if ($coroutine instanceof \Generator) {
            $task = new Task($coroutine, $context, $taskId, $parentTask);
            $task->run();

            return $task;
        }

        return $coroutine;
    }

    public function __construct(\Generator $coroutine, Context $context = null, $taskId = 0, Task $parentTask = null)
    {
        $this->coroutine = $coroutine;
        $this->taskId = $taskId ? $taskId : TaskId::create();
        $this->parentTask = $parentTask;

        if ($context) {
            $this->context = $context;
        } else {
            $this->context = new Context();
        }

        $this->scheduler = new Scheduler($this);
    }

    public function run()
    {
        while (true) {
            try {
                if ($this->status === Signal::TASK_KILLED) {
                    $this->fireTaskDoneEvent();
                    $this->status = Signal::TASK_DONE;
                    break;
                }
                $this->status = $this->scheduler->schedule();
                switch ($this->status) {
                    case Signal::TASK_KILLED:
                        return null;
                    case Signal::TASK_SLEEP:
                        return null;
                    case Signal::TASK_WAIT:
                        return null;
                    case Signal::TASK_DONE:
                        $this->fireTaskDoneEvent();
                        return null;
                    default:
                        continue;
                }
            } catch (\Throwable $t) {
                $this->scheduler->throwException($t);
            } catch (\Exception $e) {
                $this->scheduler->throwException($e);
            }
        }
    }

    public function sendException($e)
    {
        $this->scheduler->throwException($e);
    }

    public function send($value)
    {
        try {
            $this->sendValue = $value;
            return $this->coroutine->send($value);
        } catch (\Throwable $t) {
            $this->sendException($t);
        } catch (\Exception $e) {
            $this->sendException($e);
        }
    }

    public function getTaskId()
    {
        return $this->taskId;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function getSendValue()
    {
        return $this->sendValue;
    }

    public function getResult()
    {
        return $this->sendValue;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($signal)
    {
        $this->status = $signal;
    }

    public function getCoroutine()
    {
        return $this->coroutine;
    }

    public function setCoroutine(\Generator $coroutine)
    {
        $this->coroutine = $coroutine;
    }

    public function getParentTask()
    {
        return $this->parentTask;
    }

    public function fireTaskDoneEvent()
    {
        if (null === $this->context) {
            return;
        }
        $evtName = 'task_event_' . $this->taskId;
        $this->context->getEvent()->fire($evtName, $this->sendValue);
    }
}
