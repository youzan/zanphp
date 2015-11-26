<?php
namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Network\Contract\Response;

class Scheduler
{
    private $task = null;
    private $stack = null;

    public function __construct(Task $task){
        $this->task = $task;
        $this->stack = new \SplStack();
    }

    public function schedule() {
        $coroutine = $this->task->getCoroutine();
        $value = $coroutine->current();

        $signal = $this->handleSysCall($value);
        if($signal !== null)  return $signal;

        $signal = $this->handleCoroutine($value);
        if($signal !== null)  return $signal;

        $signal = $this->handleAsyncJob($value);
        if($signal !== null)  return $signal;

        $signal = $this->handleAsyncCallback($value);
        if($signal !== null)  return $signal;

        $signal = $this->handleTaskStack($value);
        if($signal !== null)  return $signal;

        return Signal::TASK_CONTINUE;
    }

    public function asyncCallback(Response $response) {

    }

    private function handleSysCall($value) {
        if ( !($value instanceof SysCall) ) {
            return null;
        }

        $signal = call_user_func($value, $this->task);
        if(Signal::isSignal($signal)) {
            return $signal;
        }

        return null;
    }

    private function handleCoroutine($value) {
        if ($value instanceof \Generator) {
            return null;
        }

        $coroutine = $this->task->getCoroutine();
        $this->stack->push($coroutine);
        $this->task->setCoroutine($value);

        return Signal::TASK_CONTINUE;
    }

    private function handleAsyncJob($value) {
        if(!is_subclass_of($value, '\\Zan\\Framework\\Foundation\\Contract\\Async')) {
            return null;
        }

        $coroutine = $this->task->getCoroutine();
        $this->stack->push($coroutine);
        $value->execute([$this,'asyncCallback']);

        return Signal::TASK_SLEEP;
    }

    private function handleAsyncCallback($value) {
        if(Signal::TASK_SLEEP !== $this->task->getStatus()) {
            return null;
        }

        $this->task->setStatus(Signal::TASK_RUNNING);

        return null;
    }

    private function handleTaskStack($value) {
        return null;
    }

    private function isStackEmpty() {
        return $this->stack->isEmpty();
    }
}