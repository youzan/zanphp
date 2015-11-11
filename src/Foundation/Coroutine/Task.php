<?php
/**
 * Created by IntelliJ IDEA.
 * User: winglechen
 * Date: 15/11/9
 * Time: 13:37
 */

namespace Zan\Framework\Foundation\Coroutine;

class Task {
    protected $taskId = 0;
    protected $coroutine = null;
    protected $stack = null;

    protected $sendValue = null;
    protected $scheduler = null;
    protected $status = 0;

    public function __construct(\Generator $coroutine, $taskId=0) {
        $this->coroutine = $coroutine;
        $this->taskId = $taskId;
        $this->stack = new \SplStack();
        $this->scheduler = new Scheduler($this);
    }

    public function run(){
        while (true) {
            try {
                $this->status = $this->scheduler->schedule();
                switch($this->status) {
                    case Signal::TASK_KILLED:
                        return null;
                    case Signal::TASK_SLEEP:
                        return null;
                }
            } catch (\Exception $e) {
                $this->coroutine->throw($e);
            }
        }
    }

    public function getTaskId() {
        return $this->taskId;
    }

    public function setSendValue($sendValue) {
        $this->sendValue = $sendValue;
    }

    public function getStatus() {
        return $this->status;
    }

    public function setStatus($signal) {
        $this->status = $signal;
    }

    public function getCoroutine() {
        return $this->coroutine;
    }

    public function setCoroutine(\Generator $coroutine) {
        $this->coroutine = $coroutine;
    }

    public function pushStack(\Generator $coroutine) {
        $this->stack->push($coroutine);
    }

    public function popStack() {
        return $this->stack->pop();
    }

    public function isStackEmpty() {
        return $this->stack->isEmpty();
    }


}