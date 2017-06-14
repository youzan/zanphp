<?php

namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Foundation\Exception\ParallelException;
use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Parallel
{
    private $task;
    private $childTasks = [];
    private $sendValues = [];
    private $exceptions = [];

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function call($coroutines)
    {
        $parentTaskId = $this->task->getTaskId();
        $taskContext = $this->task->getContext();
        
        $taskDoneEventName = 'parallel_task_done_' . $parentTaskId;
        $event = $this->task->getContext()->getEvent();
        $eventChain = $event->getEventChain();
        
        $event->bind($taskDoneEventName, [$this,'done']);

        foreach($coroutines as $key => $coroutine) {
            if ($coroutine instanceof SysCall) {
                throw new InvalidArgumentException('can not run syscall in parallel');
            }
            
            if(!($coroutine instanceof \Generator)) {
                $this->sendValues[$key] = $coroutine;
                continue; 
            }

            $childTask = new Task($this->catchException($key, $coroutine), $taskContext, 0, $this->task);
            $this->childTasks[$key] = $childTask;

            $newTaskId = $childTask->getTaskId();
            $evtName = 'task_event_' . $newTaskId;
            $eventChain->before($evtName, $taskDoneEventName);
        }

        if ($this->childTasks == []) {
            $event->fire($taskDoneEventName);
        }
        
        foreach ($this->childTasks as $childTask){
            $childTask->run();
        }
    }

    public function done()
    {
        $event = $this->task->getContext()->getEvent();
        $eventChain = $event->getEventChain();
        $parentTaskId = $this->task->getTaskId();
        $taskDoneEventName = 'parallel_task_done_' . $parentTaskId;

        foreach ($this->childTasks as $key => $childTask) {
            $this->sendValues[$key] = $childTask->getResult();

            $newTaskId = $childTask->getTaskId();
            $evtName = 'task_event_' . $newTaskId;
            $eventChain->breakChain($evtName, $taskDoneEventName);
        }

        $event->unregister($taskDoneEventName);

        if (empty($this->exceptions)) {
            $this->task->send($this->sendValues);
            $this->task->run();
        } else {
            $ex = ParallelException::makeWithResult($this->sendValues, $this->exceptions);
            $this->task->getCoroutine()->throw($ex);
            $this->task->run();
        }
    }

    private function catchException($key, \Generator $coroutine)
    {
        try {
            yield $coroutine;
            return;
        } catch (\Throwable $t) {
            $ex = t2ex($t);
        } catch (\Exception $ex) { }

            echo_exception($ex);
            $this->exceptions[$key] = $ex;
            yield $ex;
    }
}
