<?php

namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Parallel
{
    private $task;
    private $childTasks = [];
    private $sendValues = [];

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

            $childTask = Task::execute($coroutine, $taskContext, 0, $this->task);
            $this->childTasks[$key] = $childTask;

            $newTaskId = $childTask->getTaskId();
            $evtName = 'task_event_' . $newTaskId;
            $eventChain->before($evtName, $taskDoneEventName);
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

        $this->task->send($this->sendValues);
        $this->task->run();
    }
}
