<?php

namespace Zan\Framework\Foundation\Coroutine;

class Scheduler
{
    private $task = null;
    private $stack = null;

    public function __construct(Task $task)
    {
        $this->task = $task;
        $this->stack = new \SplStack();
    }

    public function schedule()
    {
        $coroutine = $this->task->getCoroutine();
        $value = $coroutine->current();

        $signal = $this->handleSysCall($value);
        if ($signal !== null) return $signal;

        $signal = $this->handleCoroutine($value);
        if ($signal !== null) return $signal;

        $signal = $this->handleAsyncJob($value);
        if ($signal !== null) return $signal;

//        $signal = $this->handleAsyncCallback($value);
//        if ($signal !== null) return $signal;

        $signal = $this->handleYieldValue($value);
        if ($signal !== null) return $signal;

        $signal = $this->handleTaskStack($value);
        if ($signal !== null) return $signal;

        $signal = $this->checkTaskDone($value);
        if ($signal !== null) return $signal;

        return Signal::TASK_CONTINUE;
    }

    public function isStackEmpty()
    {
        return $this->stack->isEmpty();
    }

    public function throwException($e)
    {
        $coroutine = $this->stack->pop();
        $coroutine->throw($e);

        $this->task->setCoroutine($coroutine);
    }

    public function asyncCallback($response)
    {
        $coroutine = $this->stack->pop();
        $this->task->setCoroutine($coroutine);
        $this->task->send($response);
        $this->task->run();
    }

    //TODO:  move handlers out of this class
    private function handleSysCall($value)
    {
        if (!($value instanceof SysCall)
            && !is_subclass_of($value, '\\Zan\\Framework\\Foundation\\Coroutine\\Syscall')
        ) {
            return null;
        }

        $signal = call_user_func($value, $this->task);
        if (Signal::isSignal($signal)) {
            return $signal;
        }

        return null;
    }

    private function handleCoroutine($value)
    {
        if (!($value instanceof \Generator)) {
            return null;
        }

        $coroutine = $this->task->getCoroutine();
        $this->stack->push($coroutine);
        $this->task->setCoroutine($value);

        return Signal::TASK_CONTINUE;
    }

    private function handleAsyncJob($value)
    {
        if (!is_subclass_of($value, '\\Zan\\Framework\\Foundation\\Contract\\Async')) {
            return null;
        }

        $coroutine = $this->task->getCoroutine();
        $this->stack->push($coroutine);
        $value->execute([$this, 'asyncCallback']);

        return Signal::TASK_WAIT;
    }

    private function handleAsyncCallback($value)
    {
        if (Signal::TASK_WAIT !== $this->task->getStatus()) {
            return null;
        }

        if (is_null($value) && !$this->isStackEmpty()) {
            $coroutine = $this->stack->pop();
            $coroutine->send($this->task->getSendValue());
        }

        return Signal::TASK_CONTINUE;
    }

    private function handleTaskStack($value)
    {
        if ($this->isStackEmpty()) {
            return null;
        }

        $coroutine = $this->stack->pop();
        $this->task->setCoroutine($coroutine);

        $value = $this->task->getSendValue();
        $this->task->send($value);

        return Signal::TASK_CONTINUE;
    }

    private function handleYieldValue($value)
    {
        $coroutine = $this->task->getCoroutine();
        if (!$coroutine->valid()) {
            return null;
        }

        $this->task->send($value);
        return Signal::TASK_CONTINUE;
    }

    private function checkTaskDone($value)
    {
        $coroutine = $this->task->getCoroutine();
        if ($coroutine->valid()) {
            return null;
        }

        return Signal::TASK_DONE;
    }
}