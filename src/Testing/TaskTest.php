<?php

namespace Zan\Framework\Testing;

use Zan\Framework\Foundation\Coroutine\Event;
use Zan\Framework\Foundation\Coroutine\Task;
use Zan\Framework\Network\Connection\ConnectionInitiator;
use Zan\Framework\Store\Database\Sql\SqlMapInitiator;
use Zan\Framework\Store\Database\Sql\Table;
use Zan\Framework\Utilities\DesignPattern\Context;
use Zan\Framework\Utilities\Types\Time;

class TaskTest extends UnitTest
{
    public static $isInitialized = false;
    public static  $isRunningJob = false;
    private static $jobs = [];
    private static $nTasks = 0;

    public $event = null;
    public $eventChain = null;
    protected $taskMethodPattern = '/^task.+/i';
    protected $taskCounter = 0;
    protected $coroutines = [];

    public function testTasksWork()
    {
        self::$nTasks++;
        self::$jobs[] = $this;

        if (self::$isRunningJob) {
            return;
        }

        $task = array_shift(self::$jobs);
        $task->initTask();
        $task->taskCounter++;
        $task->eventChain->before('test_task_num_' . $task->taskCounter, 'test_task_done');
        $task->scanTasks();
        $taskCoroutine = $task->runTaskTests();
        self::$isRunningJob = true;
        $context = new Context();
        $context->set('request_time', Time::stamp());
        $request_timeout = 30;
        $context->set('request_timeout', $request_timeout);
        Task::execute($taskCoroutine, $context);
    }
    
    protected function scanTasks()
    {
        $ref = new \ReflectionClass($this);
        $methods = $ref->getMethods(\ReflectionMethod::IS_PROTECTED | \ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            $methodName = $method->getName();
            if (!preg_match($this->taskMethodPattern, $methodName)) {
                continue;
            }

            $coroutine = $this->$methodName();
            $this->coroutines[] = $coroutine;
        } 
    }

    protected function initTask()
    {
        if (!self::$isInitialized) {
            //sql map
            SqlMapInitiator::getInstance()->init();
            //table
            Table::getInstance()->init();
            //connection pool init
            ConnectionInitiator::getInstance()->init('connection', null);
            self::$isInitialized = true;
        }

        $this->event = new Event();
        $this->eventChain = $this->event->getEventChain();
        
        $this->event->bind('test_task_done', function () {
            --self::$nTasks;
            if (self::$jobs == []) {
                self::$isRunningJob = false;
            } else {
                $task = array_shift(self::$jobs);
                $task->initTask();
                $task->taskCounter++;
                $task->eventChain->before('test_task_num_' . $task->taskCounter, 'test_task_done');
                $task->scanTasks();
                $taskCoroutine = $task->runTaskTests();
                $context = new Context();
                $context->set('request_time', Time::stamp());
                $request_timeout = 30;
                $context->set('request_timeout', $request_timeout);
                Task::execute($taskCoroutine, $context);
                return;
            }
            if (self::$nTasks == 0) {
                swoole_event_exit();
            }
        });
    }
    
    protected function runTaskTests()
    {
        yield parallel($this->coroutines);
        $this->event->fire('test_task_done');
    }

}





