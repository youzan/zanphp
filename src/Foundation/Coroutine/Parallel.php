<?php

namespace Zan\Framework\Foundation\Coroutine;

use Zan\Framework\Foundation\Exception\System\InvalidArgumentException;

class Parallel
{
    private $task;
    private $taskDoneEventName;
    private $childTasks = [];
    private $sendValues = [];

    public function __construct(Task $task)
    {
        $this->task = $task;
    }

    public function call($coroutines)
    {
        $this->taskDoneEventName = 'parallel_task_done_' . $this->task->getTaskId();
        $this->task->getContext()->getEvent()->bind($this->taskDoneEventName, $this->run());

        foreach($coroutines as $key => $coroutine) {

            if ($coroutine instanceof SysCall) {
                throw new InvalidArgumentException();
            }

            if ($coroutine instanceof \Generator) {
                $newTask = new Task($coroutine, $this->task->getContext(), 0, $this->task->getTaskId());
                $newTaskId = $newTask->getTaskId();
                $this->childTasks[$key] = $newTask;
                $this->sendValues[$key] = null;

                $evtName = 'task_event_' . $newTaskId;
                $this->task->getContext()->getEvent()->getEventChain()->after($evtName, $this->taskDoneEventName);
            } else {
                $this->sendValues[$key] = $coroutine;
            }
        }

        foreach ($this->childTasks as $childTask) {
            /** @var $childTask Task */
            $childTask->run();
        }
    }

    private function run()
    {
        return function() {
            $isOver = true;
            foreach ($this->childTasks as $key => $childTask) {
                /** @var $childTask Task */
                $this->sendValues[$key] = $childTask->getResult();

                if (is_null($this->sendValues[$key])) {
                    $isOver = false;
                }
            }

            if ($isOver and !empty($this->sendValues)) {
                $this->task->send($this->sendValues);
                $this->task->run();
            }
        };
    }
}