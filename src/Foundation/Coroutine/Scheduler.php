<?php

namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Foundation\Contract\Async;
use Zan\Framework\Network\Exception\ServerTimeoutException;
use Zan\Framework\Utilities\Types\Time;

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

        $signal = $this->handleYieldValue($value);
        if ($signal !== null) return $signal;

        $signal = $this->handleTaskStack($value);
        if ($signal !== null) return $signal;


        $signal = $this->checkTaskDone($value);
        if ($signal !== null) return $signal;

        return Signal::TASK_DONE;
    }

    public function isStackEmpty()
    {
        return $this->stack->isEmpty();
    }

    public function throwException($e, $isFirstCall = false)
    {
        if ($this->isStackEmpty()) {
            $this->task->getCoroutine()->throw($e);
            return;
        }

        try{
            if ($isFirstCall) {
                $coroutine = $this->task->getCoroutine();
            } else {
                $coroutine = $this->stack->pop();
            }

            $this->task->setCoroutine($coroutine);
            $coroutine->throw($e);

            $this->task->run();
        }catch (\Exception $e){
            $this->throwException($e);
        }
    }

    public function asyncCallback($response, $exception = null)
    {
        $context = $this->task->getContext();
        $request_time = $context->get('request_time');
        $request_timeout = $context->get('request_timeout');
        $now_time = Time::stamp();
        
        //超时处理
        if (($now_time - $request_time > $request_timeout) && !$exception) {
            $exception = new ServerTimeoutException('Maximum execution time of '
                                                    . $request_timeout
                                                    . ' seconds exceeded');
        }

        if ($exception !== null
            && $exception instanceof \Exception) {
                $this->throwException($exception, true);
        } else {
            $this->task->send($response);
            $this->task->run();
        }
    }

    //TODO:  move handlers out of this class
    private function handleSysCall($value)
    {
        if (!($value instanceof SysCall)
            && !is_subclass_of($value, SysCall::class)
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
        if (!is_subclass_of($value, Async::class)) {
            return null;
        }

        $value->execute([$this, 'asyncCallback']);

        return Signal::TASK_WAIT;
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

        $status = $this->task->send($value);
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
