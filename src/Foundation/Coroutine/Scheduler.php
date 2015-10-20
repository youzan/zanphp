<?php
namespace Zan\Framework\Foundation\Coroutine;

class Scheduler
{

    protected $maxTaskId = 0;
    protected $taskQueue;

    public function __construct()
    {

        $this->taskQueue = new \SplQueue();
    }

    public function newTask(\Generator $coroutine)
    {

        $taskId = ++$this->maxTaskId;
        $task = new Task($taskId, $coroutine);
        $this->taskQueue->enqueue($task);
    }

    public function schedule(Task $task)
    {

        $this->taskQueue->enqueue($task);
    }

    public function run()
    {

        while (!$this->taskQueue->isEmpty()) {
            $task = $this->taskQueue->dequeue();
            $task->run($task->getCoroutine());
        }
    }


}